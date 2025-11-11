<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Report;
use App\Models\Barangay;
use App\Models\Sitio;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Services\FirebaseService; 

class ReportIssueController extends Controller
{
    protected $firebaseService;

    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
    }

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
            'issue_type' => 'required|string|max:255', 
            'custom_issue_description' => 'nullable|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:10000',
            'barangay_id' => 'required|exists:barangays,id',
            'sitio_id' => 'required|exists:sitios,id',
            'sender_name' => 'required|string|max:255',
            'contact_number' => 'required|numeric|digits_between:1,20',
            'remarks' => 'nullable|string',
            'g-recaptcha-response' => 'required|string',
            'latitude' => 'sometimes|nullable|numeric|between:-90,90',
            'longitude' => 'sometimes|nullable|numeric|between:-180,180',
            'gps_accuracy' => 'sometimes|nullable|numeric|min:0',
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

        $today = Carbon::today()->format('Ymd');
        
        $lastReport = Report::whereDate('created_at', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastReport && preg_match('/CW-' . $today . '-(\d+)/', $lastReport->tracking_code, $matches)) {
            $sequence = str_pad((int)$matches[1] + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $sequence = '001';
        }

        $trackingCode = "CW-{$today}-{$sequence}";

        $displayIssueType = $validated['issue_type'];
        
        if ($validated['issue_type'] === 'Other' && !empty($validated['custom_issue_description'])) {
            $displayIssueType = $validated['custom_issue_description'];
        }

        $issueType = \App\Models\IssueType::where('name', $validated['issue_type'])->first();
        
        if ($issueType) {
            $priority = ucfirst($issueType->priority_level);
        } else {
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
        }

        // Retry logic for duplicates
        $maxRetries = 3;
        $retryCount = 0;
        $report = null;
        
        while ($retryCount < $maxRetries) {
            try {
                $report = Report::create([
                    'tracking_code' => $trackingCode,
                    'title' => $validated['title'],
                    'issue_type' => $displayIssueType, 
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
                    'latitude' => isset($validated['latitude']) ? (float)$validated['latitude'] : null,
                    'longitude' => isset($validated['longitude']) ? (float)$validated['longitude'] : null,
                    'gps_accuracy' => isset($validated['gps_accuracy']) ? (float)$validated['gps_accuracy'] : null,
                ]);

                break;

            } catch (\Illuminate\Database\QueryException $e) {
                if (str_contains($e->getMessage(), 'Duplicate entry') && str_contains($e->getMessage(), 'tracking_code_unique')) {
                    $retryCount++;
                    
                    $sequence = str_pad((int)$sequence + 1, 3, '0', STR_PAD_LEFT);
                    $trackingCode = "CW-{$today}-{$sequence}";
                    
                    if ($retryCount === $maxRetries) {
                        Log::error('Failed to generate unique tracking code after ' . $maxRetries . ' attempts. Last attempted code: ' . $trackingCode);
                        return back()->withErrors(['error' => 'Unable to generate unique tracking code. Please try again.']);
                    }
                    
                    continue;
                }
                throw $e;
            }
        }

        // Firebase integration
        try {
            Log::info("🔄 Attempting to send report {$report->id} to Firebase");
            
            $firebaseData = [
                'type' => $displayIssueType, // Use the actual description here too
                'severity' => strtolower($priority),
                'description' => $validated['description'],
                'location' => $barangay->name . ', ' . $sitio->name
            ];

            $result = $this->firebaseService->sendReportToFirestore($report->id, $firebaseData);
            
            if ($result) {
                Log::info("✅ Successfully sent to Firebase for report ID: {$report->id}");
            } else {
                Log::error("❌ Failed to send to Firebase for report ID: {$report->id}");
            }
            
        } catch (\Exception $e) {
            Log::error("🔥 Firebase Error in controller: " . $e->getMessage());
        }

        return Inertia::render('ReportIssue', [
            'barangays' => Barangay::with(['sitios' => function($query) {
                $query->select('id', 'name', 'barangay_id', 'is_available');
            }])->select('id', 'name', 'is_available', 'description')->get(),
            'tracking_code' => $trackingCode,
            'show_success_modal' => true,
            'status' => 'success',
        ]);
    }

    public function checkEmergency(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:5120'
        ]);

        $filename = $request->file('image')->getClientOriginalName();
        $filenameLower = strtolower($filename);
        
        // DIRECT LOGIC - no service class
        $emergencyWords = ['fire', 'burning', 'ambulance', 'accident', 'crash', 'demo', 'test', 'emergency'];
        
        $isEmergency = false;
        foreach ($emergencyWords as $word) {
            if (str_contains($filenameLower, $word)) {
                $isEmergency = true;
                break;
            }
        }
        
        \Log::info("DIRECT CHECK: {$filename} -> " . ($isEmergency ? 'EMERGENCY' : 'SAFE'));
        
        return response()->json([
            'is_emergency' => $isEmergency,
            'message' => $isEmergency ? 'Emergency detected' : 'No emergency',
            'debug' => "Filename: {$filename}"
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
