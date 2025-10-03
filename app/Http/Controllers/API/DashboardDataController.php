<?php
// app/Http/Controllers/API/DashboardDataController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\JsonResponse;

class DashboardDataController extends Controller
{
    public function getDashboardData(): JsonResponse
    {
        $pending = Report::where('status', 'pending')->count();
        $inProgress = Report::where('status', 'in_progress')->count();
        $resolved = Report::where('status', 'resolved')->count();
        $rejected = Report::where('status', 'rejected')->count();
        
        // Get monthly data for charts
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

        return response()->json([
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
            'monthlyReports' => $monthlyReports,
            'last_updated' => now()->toISOString()
        ]);
    }
}