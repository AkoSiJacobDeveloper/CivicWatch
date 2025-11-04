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
            'rating'
        );

        if ($request->sort === 'asc') {
            $query->orderBy('created_at', 'asc'); 
        } else {
            $query->latest();
        }

        $reviews = $query->paginate(6);

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

        Review::create($validated);

        return redirect()->route('review.index')
        ->with('message', 'Review Submitted Successfully!');
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
        ->inRandomOrder()
        ->take(3)
        ->get();

        return Inertia::render('Welcome', [
            'reviews' => $reviews
        ]);
    }

    public function showInAdmin(Request $request) {
        $query = Review::select(
            'id',
            'name',
            'location',
            'review_message',
            'is_anonymous',
            'created_at',
            'rating'
        );

        if ($request->sort === 'asc') {
            $query->orderBy('created_at', 'asc'); 
        } else {
            $query->latest();
        }

        $reviews = $query->paginate(6);

        return Inertia::render('Admin/Reviews', [
            'reviews' => $reviews,
            'filters' => $request->only('sort'),
        ]);
    }

    public function destroy($id) {
        $review = Review::findOrFail($id);
        $review->delete();

        return redirect()->back()->with('success', 'Review deleted successfully.');
    }
}
