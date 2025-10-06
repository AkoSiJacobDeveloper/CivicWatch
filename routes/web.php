<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BarangayController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportIssueController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\PendingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\ReviewController;
use App\Models\Barangay;
use App\Http\Controllers\TrackReportController;
use App\Http\Controllers\Api\LocationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn() => Inertia::render('Welcome'));
Route::get('/track-reports', fn() => Inertia::render('TrackReports'));
Route::get('faq', fn() => Inertia::render('FAQ'));
Route::get('/about', fn() => Inertia::render('About'));
Route::get('/review', fn() => Inertia::render('Review'));

Route::prefix('report-issue')->name('report.')->group(function() {
    Route::get('/', fn() => Inertia::render('ReportIssue'));
    Route::post('/', [ReportIssueController::class, 'store'])->name('report.store');
});

Route::prefix('contact-us')->name('contact.')->group(function() {
    Route::get('/', fn() => Inertia::render('Contact-Us'));
    Route::post('/', [ContactController::class, 'store'])->name('store');
});

// Tracking Reports
Route::get('/track-reports', [TrackReportController::class, 'index'])->name('trackreports');

// Polling
Route::get('/api/dashboard-data', [App\Http\Controllers\API\DashboardDataController::class, 'getDashboardData'])
    ->name('api.dashboard-data');
Route::get('/api/reports-data', function() {
    $totalReports = \App\Models\Report::count();
    
    return response()->json([
        'totalReports' => $totalReports,
        'timestamp' => now()->toISOString()
    ]);
});

Route::middleware(['auth', \App\Http\Middleware\isAdmin::class])->group(function () {
    Route::get('Admin/Dashboard', [ReportsController::class, 'index'])->name('admin.dashboard');
});

Route::prefix('admin')->group(function () {    
    Route::get('/admin-login', [AuthenticatedSessionController::class, 'create'])->name('admin.login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    // Protected admin routes
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/reports/trash', [ReportsController::class, 'trash'])->name('report.trash');

        Route::get('/dashboard', [ReportsController::class, 'getReportCounts'])->name('admin.dashboard');
        Route::get('/reports', [ReportsController::class, 'index'])->name('admin.reports');
        Route::get('/pending-reports', [ReportsController::class, 'pendingReports'])->name('admin.pending-reports');
        Route::get('/in-progress', [ReportsController::class, 'inProgress'])->name('admin.in-progress');
        Route::get('/resolved-reports', [ReportsController::class, 'resolvedReports'])->name('admin.resolved-reports');
        Route::get('/rejected-reports', [ReportsController::class, 'rejectedReports'])->name('admin.rejected-reports');
        Route::get('/reports/{id}', [ReportsController::class, 'show'])->name('reports.show');
        Route::delete('/reports/{id}', [ReportsController::class, 'destroy'])->name('reports.destroy');
        Route::put('/reports/approve/{report}', [ReportsController::class, 'approve'])->name('report.approve');
        Route::put('/reports/resolved/{report}', [ReportsController::class, 'resolved'])->name('report.resolved');
        Route::put('/reports/reject/{report}', [ReportsController::class, 'reject'])->name('report.rejected');

        // Bulk Actions
        Route::post('/reports/mark-duplicate', [ReportsController::class, 'markAsDuplicate'])->name('admin.reports.mark-duplicate');
        Route::post('/reports/bulk-approve', [ReportsController::class, 'bulkApprove'])->name('admin.reports.bulk-approve');
        Route::post('/reports/bulk-resolved', [ReportsController::class, 'bulkResolved'])->name('admin.reports.bulk-resolve');
        Route::post('/reports/bulk-delete', [ReportsController::class, 'bulkDelete'])->name('admin.reports.bulk-delete');
        Route::post('/reports/bulk-revert', [ReportsController::class, 'bulkRevert'])->name('admin.reports.bulk-revert');

        // Soft delete routes
        Route::post('/reports/{report}/restore', [ReportsController::class, 'restore'])->name('admin.reports.restore');
        Route::delete('/reports/{report}/force-delete', [ReportsController::class, 'forceDelete'])->name('admin.reports.force-delete');

        // Review
        Route::get('/reviews', [ReviewController::class, 'showInAdmin'])->name('system.review');
        Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])->name('reviews.delete');

        Route::get('/locations', fn() => Inertia::render('Admin/Locations'));
        
        // Issue Type Route
        Route::get('/issue-type', [IssueController::class, 'index'])->name('admin.issue-type.index');
        Route::post('/issue-type', [IssueController::class, 'store'])->name('admin.issue-type.store');
        Route::put('/issue-type/{issueType}', [IssueController::class, 'update'])->name('admin.issue-type.update');
        Route::delete('/issue-type/{issueType}', [IssueController::class, 'destroy'])->name('admin.issue-type.delete');

        
    });
});

// Users Review
Route::get('/review', [ReviewController::class, 'index'])->name('review.index');
Route::post('/review', [ReviewController::class, 'store'])->name('review.store');
Route::get('/', [ReviewController::class, 'showInHome'])->name('review.showInHome');

Route::get('/barangays-with-sitios', function () {
    return Barangay::with(['sitios'])->get();
});

// Route::get('/preview-email', function() {
//     return new App\Mail\ContactFormSubmitted([
//         'name' => 'Test User',
//         'email' => 'test@example.com',
//         'subject' => 'Test Subject',
//         'message' => 'This is a test message to check the email design.'
//     ]);
// });

require __DIR__.'/auth.php';
