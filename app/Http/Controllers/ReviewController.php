<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function index(Request $request) {
    $query = Review::select(
        'id',
        'name',
        'location',
        'review_message',
        'is_anonymous',
        'created_at',
        'rating',
        'status'
    )->approved();

    $sort = $request->get('sort', 'desc');
    
    if ($sort === 'asc') {
        $query->orderBy('created_at', 'asc'); 
    } else {
        $query->latest();
    }

    $reviews = $query->paginate(6);

    // Return the paginator instance directly - Inertia will handle it
    return Inertia::render('Review', [
        'reviews' => $reviews,
        'filters' => $request->only(['sort'])
    ]);
}

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'string',
            'review_message' => 'required|string|max:5000',
            'is_anonymous' => 'boolean',
            'rating' => 'nullable|integer|min:1|max:5'
        ]);

        // Add pending status by default
        $validated['status'] = 'pending';

        Review::create($validated);

        return redirect()->route('review.index')
            ->with('message', 'Review Submitted Successfully! It will be visible after admin approval.');
    }

    public function showInHome() {
        $reviews = Review::select(
            'id',
            'name',
            'location',
            'review_message',
            'is_anonymous',
            'created_at',
            'rating'
        )
        ->approved()
        ->inRandomOrder()
        ->take(3)
        ->get();

        return Inertia::render('Welcome', [
            'reviews' => $reviews
        ]);
    }

    public function showInAdmin(Request $request) {
    $status = $request->status ?? 'pending';
    
    $query = Review::query();

    if ($status === 'pending') {
        $query->pending();
    } elseif ($status === 'approved') {
        $query->approved();
    } elseif ($status === 'rejected') {
        $query->rejected();
    }

    if ($request->sort === 'asc') {
        $query->orderBy('created_at', 'asc'); 
    } else {
        $query->latest();
    }

    $reviews = $query->paginate(6);

    // Get counts using a single query for better performance
    $counts = Review::selectRaw('
        COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count,
        COUNT(CASE WHEN status = "approved" THEN 1 END) as approved_count,
        COUNT(CASE WHEN status = "rejected" THEN 1 END) as rejected_count
    ')->first();

    return Inertia::render('Admin/Reviews', [
        'reviews' => $reviews,
        'pending_count' => $counts->pending_count ?? 0,
        'approved_count' => $counts->approved_count ?? 0,
        'rejected_count' => $counts->rejected_count ?? 0,
        'filters' => $request->only(['sort', 'status']),
        'currentStatus' => $status
    ]);
}

    public function approve($id) {
        $review = Review::findOrFail($id);
        $review->update(['status' => 'approved']);

        return redirect()->back()->with('success', 'Review approved successfully.');
    }

    public function reject($id) {
        $review = Review::findOrFail($id);
        $review->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Review rejected successfully.');
    }

    public function destroy($id) {
        $review = Review::findOrFail($id);
        $review->delete();

        return redirect()->back()->with('success', 'Review deleted successfully.');
    }
}