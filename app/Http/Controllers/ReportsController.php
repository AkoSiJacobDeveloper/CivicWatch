<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Barangay;
use App\Models\Sitio;
use App\Models\IssueType; 
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportsController extends Controller
{
    // Fetching All reports by categories or status
    public function index(Request $request) {
    $status = $request->query('status');

    $query = Report::with(['barangay', 'sitio'])
        ->select(
            'id',
            'tracking_code',
            'image',
            'title',
            'issue_type',
            'description',
            'barangay_name',
            'sitio_name',
            'sender_name',
            'contact_number',
            'status',
            'priority_level',
            'created_at',
            'updated_at',
            'duplicate_of_report_id'
        )
        ->latest();
    
    // Status filter
    if ($request->status && $request->status !== 'all') {
        $query->where('status', $request->status);
    }

    // Search filter
    if ($request->search) {
        $query->where('title', 'like', "%{$request->search}%")
               ->orWhere('tracking_code', 'like', "%{$request->search}%");
    }

    // Issue type filter
    if ($request->issue_type) {
        // Find the issue type name by ID
        $issueTypeName = IssueType::find($request->issue_type)?->name;
        if ($issueTypeName) {
            $query->where('issue_type', $issueTypeName);
        }
    }

    // Priority level filter 
    if ($request->priority_level) {
        $query->where('priority_level', $request->priority_level);
        Log::info('Applied priority level filter:', ['priority_level' => $request->priority_level]);
    }

    // Date range filter
    if ($request->start_date) {
        $query->whereDate('created_at', '>=', $request->start_date);
    }

    // Barangay filter
    if ($request->barangay) {
        $barangayName = Barangay::find($request->barangay)?->name;
        if ($barangayName) {
            $query->where('barangay_name', $barangayName);
        }
    }

    // Sitio filter
    if ($request->sitio) {
        $sitioName = Sitio::find($request->sitio)?->name;
        if ($sitioName) {
            $query->where('sitio_name', $sitioName);
        }
    }

    if ($request->end_date) {
        $query->whereDate('created_at', '<=', $request->end_date);
    }

    $reports = $query->paginate(10)->withQueryString();
    
    // Transform data to include related information
    $reports->getCollection()->transform(function ($report) {
        return [
            'id' => $report->id,
            'tracking_code' => $report->tracking_code,
            'image' => $report->image,
            'title' => $report->title,
            'type' => $report->issue_type ?: 'Unknown Category', 
            'description' => $report->description,
            'location' => ($report->barangay_name && $report->sitio_name) 
                ? $report->barangay_name . ', ' . $report->sitio_name 
                : ($report->barangay_name ?: 'Unknown Location'),
            'sender' => $report->sender_name,
            'contact' => $report->contact_number,
            'status' => $report->status,
            'priority_level' => $report->priority_level,
            'created_at' => $report->created_at->format('M d, Y h:i A'),
            'updated_at' => $report->updated_at->format('M d, Y h:i A'),
            'duplicate_of_report_id' => $report->duplicate_of_report_id
        ];
    });
    
        // Fetch issue types for the filter modal
        $issueTypes = IssueType::select('id', 'name')->get();
        $barangays = Barangay::select('id', 'name')->orderBy('name')->get();
        $sitios = Sitio::select('id', 'name', 'barangay_id')->with('barangay:id,name')->orderBy('name')->get();

        return Inertia::render('Admin/Reports', [
            'reports' => $reports,
            'status'  => $request->status ?? 'all',
            'issueTypes' => $issueTypes,
            'barangays' => $barangays, 
            'sitios' => $sitios,       
        ]);
    }

    // Fetching Pending Reports
    public function pendingReports() {
        $pending = Report::with(['category', 'barangay', 'sitio'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(10);
            
        // Transform data to include related information
        $pending->getCollection()->transform(function ($report) {
            return [
                'id' => $report->id,
                'tracking_code' => $report->tracking_code,
                'image' => $report->image,
                'title' => $report->title,
                'type' => $report->category ? $report->category->name : 'Unknown Category',
                'description' => $report->description,
                'location' => ($report->barangay_name && $report->sitio_name) 
                    ? $report->barangay_name . ', ' . $report->sitio_name 
                    : ($report->barangay_name ?: 'Unknown Location'),
                'sender' => $report->sender_name,
                'contact' => $report->contact_number,
                'status' => $report->status,
                'priority_level' => $report->priority_level,
                'created_at' => $report->created_at->format('M d, Y h:i A'),
                'updated_at' => $report->updated_at->format('M d, Y h:i A'),
                'duplicate_of_report_id' => $report->duplicate_of_report_id
            ];
        });

        return Inertia::render('Admin/Pending', [
            'pending' => $pending 
        ]);
    }

    // Fetching reports count for dashboard
    public function getReportCounts() {
        $pending = Report::where('status', 'pending')->count();
        $inProgress = Report::where('status', 'in_progress')->count();
        $resolved = Report::where('status', 'resolved')->count();
        $rejected = Report::where('status', 'rejected')->count();
        
        $monthlyData = Report::selectRaw('
            YEAR(created_at) as year,
            MONTH(created_at) as month,
            COUNT(*) as count
        ')
        ->whereYear('created_at', now()->year)
        ->groupBy('year', 'month')
        ->orderBy('month')
        ->get();

        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
        ];

        $monthlyReports = [];
        foreach ($months as $monthNum => $monthName) {
            $found = $monthlyData->firstWhere('month', $monthNum);
            $monthlyReports[] = [
                'month' => $monthName,
                'count' => $found ? $found->count : 0
            ];
        }
        
        return Inertia::render('Admin/Dashboard', [
            'totalReports' => $pending + $inProgress + $resolved + $rejected,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'resolved' => $resolved,
            'rejected' => $rejected,
            'reportCounts' => [
                'pending' => $pending,
                'in_progress' => $inProgress,
                'resolved' => $resolved,
                'rejected' => $rejected
            ],
            'monthlyReports' => $monthlyReports 
        ]);
    }

    // Fetching monthly reports for dashboard
    public function getMonthlyReports() {
        $monthlyData = Report::selectRaw('
            YEAR(created_at) as year,
            MONTH(created_at) as month,
            COUNT(*) as count
        ')
        ->whereYear('created_at', now()->year)
        ->groupBy('year', 'month')
        ->orderBy('month')
        ->get();

        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
        ];

        $monthlyReports = [];
        foreach ($months as $monthNum => $monthName) {
            $found = $monthlyData->firstWhere('month', $monthNum);
            $monthlyReports[] = [
                'month' => $monthName,
                'count' => $found ? $found->count : 0
            ];
        }

        return $monthlyReports;
    }

    // Fetching data for single report
    public function show($id) {
    $report = Report::with(['barangay', 'sitio', 'duplicates'])->findOrFail($id);

    Log::info('Report data from database', [
        'id' => $report->id,
        'status' => $report->status,
        'issue_type' => $report->issue_type,
        'rejection_reason' => $report->rejection_reason
    ]);
    
    $reportData = [
        'id' => $report->id,
        'tracking_code' => $report->tracking_code,
        'image' => $report->image,
        'title' => $report->title,
        'type' => $report->issue_type ?: 'Unknown Category', // Use issue_type directly
        'description' => $report->description,
        'location' => ($report->barangay_name && $report->sitio_name) 
            ? $report->barangay_name . ', ' . $report->sitio_name 
            : ($report->barangay_name ?: 'Unknown Location'),
        'barangay' => $report->barangay ? $report->barangay->name : 'Unknown Barangay',
        'sitio' => $report->sitio ? $report->sitio->name : 'Unknown Sitio',
        'sender' => $report->sender_name,
        'contact' => $report->contact_number,
        'remarks' => $report->remarks,
        'created_at' => $report->created_at->format('M d, Y h:i A'),
        'updated_at' => $report->updated_at->format('M d, Y h:i A'),
        'status' => $report->status,
        'priority_level' => $report->priority_level,
        'rejection_reason' => $report->rejection_reason,
        'duplicate_of_report_id' => $report->duplicate_of_report_id,

        'duplicates' => $report->duplicates->map(fn($dup) => [
            'id' => $dup->id,
            'tracking_code' => $dup->tracking_code,
            'title' => $dup->title,
            'status' => $dup->status,
        ]),
    ];

        Log::info('Data being sent to frontend', $reportData);

        return Inertia::render('Admin/Details/ReportDetails', [
            'report' => $reportData
        ]);
    }

    // Delete functionality
    public function destroy($id) {
        $report = Report::findOrFail($id);
        $report->delete(); 

        return redirect()->route('admin.reports')->with('success', 'Report deleted successfully!');
    }

    // Approving functionality
    public function approve(Report $report) {
        $report->update([
            'status' => 'in_progress'
        ]);

        return redirect()
            ->route('admin.reports')
            ->with('success', 'Report approved successfully!');
    }

    // Resolving functionality
    public function resolved(Report $report) {
        $report->update([
            'status' => 'resolved'
        ]);

        return redirect()
            ->route('admin.reports')
            ->with('success', 'Report resolved successfully!');
    }

    // In progress functionality
    public function inProgress() {
        $in_progress = Report::with(['category', 'barangay', 'sitio'])
            ->where('status', 'in_progress')
            ->latest()
            ->paginate(10);
            
        // Transform data to include related information
        $in_progress->getCollection()->transform(function ($report) {
            return [
                'id' => $report->id,
                'tracking_code' => $report->tracking_code,
                'image' => $report->image,
                'title' => $report->title,
                'type' => $report->category ? $report->category->name : 'Unknown Category',
                'description' => $report->description,
                'location' => ($report->barangay_name && $report->sitio_name) 
                    ? $report->barangay_name . ', ' . $report->sitio_name 
                    : ($report->barangay_name ?: 'Unknown Location'),
                'sender' => $report->sender_name,
                'contact' => $report->contact_number,
                'status' => $report->status,
                'priority_level' => $report->priority_level,
                'created_at' => $report->created_at->format('M d, Y h:i A'),
                'updated_at' => $report->updated_at->format('M d, Y h:i A'),
                'duplicate_of_report_id' => $report->duplicate_of_report_id
            ];
        });

        return Inertia::render('Admin/InProgress', [
            'in_progress' => $in_progress
        ]);
    }

    // Fetching resolved reports
    public function resolvedReports() {
        $resolved = Report::with(['category', 'barangay', 'sitio'])
            ->where('status', 'resolved')
            ->latest()
            ->paginate(10);
            
        // Transform data to include related information
        $resolved->getCollection()->transform(function ($report) {
            return [
                'id' => $report->id,
                'tracking_code' => $report->tracking_code,
                'image' => $report->image,
                'title' => $report->title,
                'type' => $report->category ? $report->category->name : 'Unknown Category',
                'description' => $report->description,
                'location' => ($report->barangay_name && $report->sitio_name) 
                    ? $report->barangay_name . ', ' . $report->sitio_name 
                    : ($report->barangay_name ?: 'Unknown Location'),
                'sender' => $report->sender_name,
                'contact' => $report->contact_number,
                'status' => $report->status,
                'priority_level' => $report->priority_level,
                'created_at' => $report->created_at->format('M d, Y h:i A'),
                'updated_at' => $report->updated_at->format('M d, Y h:i A'),
                'duplicate_of_report_id' => $report->duplicate_of_report_id
            ];
        });

        return Inertia::render('Admin/ResolvedReports', [
            'resolved' => $resolved
        ]);
    }

    // Fetching rejected reports
    public function rejectedReports() {
        $rejected = Report::with(['category', 'barangay', 'sitio'])
            ->where('status', 'rejected')
            ->latest()
            ->paginate(10);
            
        // Transform data to include related information
        $rejected->getCollection()->transform(function ($report) {
            return [
                'id' => $report->id,
                'tracking_code' => $report->tracking_code,
                'image' => $report->image,
                'title' => $report->title,
                'type' => $report->category ? $report->category->name : 'Unknown Category',
                'description' => $report->description,
                'location' => ($report->barangay_name && $report->sitio_name) 
                    ? $report->barangay_name . ', ' . $report->sitio_name 
                    : ($report->barangay_name ?: 'Unknown Location'),
                'sender' => $report->sender_name,
                'contact' => $report->contact_number,
                'status' => $report->status,
                'priority_level' => $report->priority_level,
                'rejection_reason' => $report->rejection_reason,
                'created_at' => $report->created_at->format('M d, Y h:i A'),
                'updated_at' => $report->updated_at->format('M d, Y h:i A'),
                'duplicate_of_report_id' => $report->duplicate_of_report_id
            ];
        });

        return Inertia::render('Admin/RejectedReports', [
            'rejected' => $rejected
        ]);
    }

    // Rejection functionality
    public function reject(Request $request, $id) {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $report = Report::findOrFail($id);

        Log::info('Rejecting report', [
            'report_id' => $id,
            'reason' => $request->reason,
            'current_status' => $report->status
        ]);

        $report->status = 'rejected';
        $report->rejection_reason = $request->reason;
        $report->save();

        $report->refresh();
        Log::info('After saving', [
            'status' => $report->status,
            'rejection_reason' => $report->rejection_reason
        ]);

        return redirect()
            ->route('admin.reports')
            ->with('success', 'Report rejected successfully!');
    }

    
    public function markAsDuplicate(Request $request)
    {
        // 1. Validate the incoming request
        $validated = $request->validate([
            'primary_report_id' => 'required|exists:reports,id',
            'duplicate_report_ids' => 'required|array|min:1',
            'duplicate_report_ids.*' => 'exists:reports,id|different:primary_report_id'
        ]);

        // 2. Use a database transaction for safety
        DB::beginTransaction();

        try {
            // 3. Update all duplicate reports
            Report::whereIn('id', $validated['duplicate_report_ids'])
                  ->update([
                      'status' => 'duplicate', // Make sure this matches your closed status
                      'duplicate_of_report_id' => $validated['primary_report_id'],
                  ]);

            // 4. Commit the transaction if everything is successful
            DB::commit();

            // 5. Return a success response
            return redirect()->back()->with('success', 'Reports successfully marked as duplicates.');

        } catch (\Exception $e) {
            // 6. Rollback the transaction if any error occurs
            DB::rollBack();
            Log::error('Duplicate marking failed: ' . $e->getMessage());

            // 7. Return an error response
            return redirect()->back()->with('error', 'Failed to mark reports as duplicates. Please try again.');
        }
    }

    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'report_ids' => 'required|array|min:1',
            'report_ids.*' => 'exists:reports,id'
        ]);

        Report::whereIn('id', $validated['report_ids'])
                ->update(['status' => 'in_progress']);

        return redirect()->back()->with('success', 'Reports approved successfully!');
    }

    public function bulkResolved(Request $request) {
        $validated = $request->validate([
            'report_ids' => 'required|array|min:1',
            'report_ids.*' => 'exists:reports,id'
        ]);

        Report::whereIn('id', $validated['report_ids'])
                ->update(['status' => 'resolved']);
            
        return redirect()->back()->with('success', 'Reports resolved successfully!');
    }
}