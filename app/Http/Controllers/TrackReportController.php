<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use Inertia\Inertia;

class TrackReportController extends Controller
{
    public function index(Request $request) {
        if($request->has('search') && !empty($request->search)) {
            $searchTerm = strtoupper(trim($request->search));
            $reports = Report::with('duplicates')
                ->where('tracking_code', $searchTerm)
                ->paginate(10);
        } else {
            $reports = Report::whereRaw('1 = 0')->paginate(10);
        }

        return Inertia::render('TrackReports', [
            'reports' => $reports,
            'searchTerm' => $request->search ?? ''
        ]);
    }
}