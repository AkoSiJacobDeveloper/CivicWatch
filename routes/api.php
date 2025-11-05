<?php

use App\Http\Controllers\ReportsController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\ReportIssueController;
use Illuminate\Support\Facades\Route;


Route::get('/dashboard', [ReportsController::class, 'getReportCounts'])->name('admin.dashboard');
Route::get('/issue-type', [IssueController::class, 'getActiveIssueType']);
Route::post('/check-emergency', [ReportIssueController::class, 'checkEmergency']);

