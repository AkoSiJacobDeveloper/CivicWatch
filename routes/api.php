<?php

use App\Http\Controllers\ReportsController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\ReportIssueController;
use App\Models\Report;
use Illuminate\Support\Facades\Route;


Route::get('/dashboard', [ReportsController::class, 'getReportCounts'])->name('admin.dashboard');
Route::get('/issue-type', [IssueController::class, 'getActiveIssueType']);
Route::post('/check-emergency', [ReportIssueController::class, 'checkEmergency']);

Route::get('/reports/{id}/details', function ($id) {
    $report = Report::with(['barangay', 'sitio', 'duplicates'])->findOrFail($id);
    
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
        'latitude' => $report->latitude,
        'longitude' => $report->longitude,
        'gps_accuracy' => $report->gps_accuracy,
        'duplicates' => $report->duplicates->map(fn($dup) => [
            'id' => $dup->id,
            'tracking_code' => $dup->tracking_code,
            'title' => $dup->title,
            'status' => $dup->status,
        ]),
    ];
});