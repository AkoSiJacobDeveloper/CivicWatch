<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Report;
use App\Models\Barangay;
use App\Models\Sitio;
use Carbon\Carbon;
use Inertia\Inertia;

class ReportIssueController extends Controller
{
    public function index()
    {
        $barangays = Barangay::with(['sitios' => function($query) {
            $query->select('id', 'name', 'barangay_id', 'is_available');
        }])->select('id', 'name', 'is_available', 'description')->get();

        return Inertia::render('ReportIssue', [
            'barangays' => $barangays
        ]);
    }

    public function create()
    {
        $barangays = Barangay::with(['sitios' => function($query) {
            $query->select('id', 'name', 'barangay_id', 'is_available');
        }])->select('id', 'name', 'is_available', 'description')->get();

        return Inertia::render('ReportIssue', [
            'barangays' => $barangays
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'issue_type' => 'required|string',
            'custom_issue_description' => 'nullable|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'barangay_id' => 'required|exists:barangays,id',
            'sitio_id' => 'required|exists:sitios,id',
            'sender_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'remarks' => 'nullable|string',
            'g-recaptcha-response' => 'required|string',
        ]);

        $barangay = Barangay::find($request->barangay_id);
        if (!$barangay->is_available) {
            return back()->withErrors(['barangay_id' => 'Selected barangay is not yet available for reporting.']);
        }

        $sitio = Sitio::find($request->sitio_id);
        if ($sitio->barangay_id != $request->barangay_id) {
            return back()->withErrors(['sitio_id' => 'Selected sitio does not belong to the selected barangay.']);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('report-images', 'public');
        }

        $recaptchaResponse = $request->input('g-recaptcha-response');
        $googleResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $recaptchaResponse
        ]);
        $result = $googleResponse->json();

        if (!isset($result['success']) || !$result['success']) {
            return back()->withErrors(['captcha' => 'ReCAPTCHA verification failed. Try again.']);
        }

        // Transaction for unique tracking code
        $trackingCode = DB::transaction(function () {
            $today = Carbon::today()->format('Ymd');

            $lastReport = Report::whereDate('created_at', Carbon::today())
                ->orderBy('created_at', 'desc')
                ->lockForUpdate()
                ->first();

            if ($lastReport && preg_match('/CW-' . $today . '-(\d+)/', $lastReport->tracking_code, $matches)) {
                $lastNumber = (int)$matches[1];
                $sequence = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $sequence = '001';
            }

            return "CW-$today-$sequence";
        });

        // Priority assignment
        $priority = 'Medium';
        $highPriorityTypes = ['fire', 'accident'];
        $lowPriorityTypes = [
            'Public karaoke complaints (non-urgent)',
            'Loud sounds from establishments during restricted hours'
        ];

        if (in_array($validated['issue_type'], $highPriorityTypes)) {
            $priority = 'High';
        } elseif (in_array($validated['issue_type'], $lowPriorityTypes)) {
            $priority = 'Low';
        }

        // Create the report
        $report = Report::create([
            'tracking_code' => $trackingCode,
            'title' => $validated['title'],
            'issue_type' => $validated['issue_type'],
            'custom_issue_description' => $validated['custom_issue_description'] ?? null,
            'description' => $validated['description'],
            'image' => $imagePath,
            'barangay_name' => $barangay->name,
            'sitio_name' => $sitio->name,
            'sender_name' => $validated['sender_name'],
            'contact_number' => $validated['contact_number'],
            'remarks' => $validated['remarks'],
            'priority_level' => $priority,
            'status' => 'pending',
        ]);

        return redirect()->back()->with([
            'tracking_code' => $trackingCode,
            'status' => 'success',
            'location' => "{$barangay->name}, {$sitio->name}",
        ]);
    }


    public function getBarangays()
    {
        $barangays = Barangay::with(['sitios' => function($query) {
            $query->select('id', 'name', 'barangay_id', 'is_available')
                  ->orderBy('name');
        }])
        ->select('id', 'name', 'is_available', 'description')
        ->orderBy('name')
        ->get();

        return response()->json($barangays);
    }

    public function getSitiosByBarangay($barangayId)
    {
        $barangay = Barangay::find($barangayId);
        
        if (!$barangay) {
            return response()->json(['error' => 'Barangay not found'], 404);
        }

        $sitios = Sitio::where('barangay_id', $barangayId)
                       ->select('id', 'name', 'is_available')
                       ->orderBy('name')
                       ->get();

        return response()->json($sitios);
    }

    public function show(string $id) {}
    public function edit(string $id) {}
    public function update(Request $request, string $id) {}
    public function destroy(string $id) {}
}
