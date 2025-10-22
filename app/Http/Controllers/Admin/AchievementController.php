<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\Category;
use App\Models\Location;
use App\Http\Requests\StoreAchievementRequest;
use App\Http\Requests\UpdateAchievementRequest;
use App\Models\AchievementGallery;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AchievementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $achievements = Achievement::with('category')
            ->latest()
            ->paginate(10);
        
        return Inertia::render('Admin/Achievements', [
            'achievements' => $achievements,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $locations = Location::all();

        return Inertia::render('Admin/CreateAchievements', [
            'categories' => $categories,
            'locations' => $locations,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAchievementRequest $request)
{
    $validated = $request->validated();

    // Generate unique slug from title
    $slug = Str::slug($validated['title']);
    $originalSlug = $slug;
    $counter = 1;

    // Check if slug already exists and make it unique
    while (Achievement::where('slug', $slug)->exists()) {
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }

    $validated['slug'] = $slug;
    
    // Auto-set as published
    $validated['status'] = 'published';

    // Handle featured image upload
    if ($request->hasFile('featured_image')) {
        $validated['featured_image'] = $request->file('featured_image')->store('achievements', 'public');
    }

    // Handle document attachments - store files and get paths
    $documentPaths = [];
    if ($request->hasFile('document_attachments')) {
        foreach ($request->file('document_attachments') as $file) {
            $documentPaths[] = [
                'path' => $file->store('achievements/documents', 'public'),
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
            ];
        }
    }
    
    // Convert array to JSON string for database storage
    $validated['document_attachments'] = !empty($documentPaths) ? $documentPaths : null;


    // Create the achievement first
    $achievement = Achievement::create($validated);

    // Handle gallery images
    if ($request->hasFile('gallery_images')) {
        foreach ($request->file('gallery_images') as $index => $file) {
            AchievementGallery::create([
                'achievement_id' => $achievement->id,
                'image_path' => $file->store('achievements/gallery', 'public'),
                'caption' => '', // You can add caption input later
                'order_index' => $index
            ]);
        }
    }

    return redirect()->back()->with('success', 'Achievement recorded successfully');
}
    /**
     * Display the specified resource.
     */
    public function show(Achievement $achievement)
    {
        // We'll implement this for public viewing later
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Achievement $achievement)
    {
        $categories = Category::all();

        return Inertia::render('Admin/Achievements/Edit', [
            'achievement' => $achievement->load('category'),
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAchievementRequest $request, Achievement $achievement)
    {
        $validated = $request->validated();

        // Update slug if title changed
        if ($achievement->title !== $validated['title']) {
            $slug = Str::slug($validated['title']);
            $originalSlug = $slug;
            $counter = 1;

            // Check if slug already exists (excluding current achievement) and make it unique
            while (Achievement::where('slug', $slug)->where('id', '!=', $achievement->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $validated['slug'] = $slug;
        }

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            // Delete old image if exists
            if ($achievement->featured_image) {
                Storage::disk('public')->delete($achievement->featured_image);
            }
            $validated['featured_image'] = $request->file('featured_image')->store('achievements', 'public');
        }

        // Handle document attachments - use the existing array directly
        $documentPaths = $achievement->document_attachments ?? [];
    
        if ($request->hasFile('document_attachments')) {
            // Delete old documents if they exist
            if (!empty($documentPaths)) {
                foreach ($documentPaths as $docData) {
                    Storage::disk('public')->delete($docData['path']);
                }
            }
            
            // Store new documents with original names
            $documentPaths = [];
            foreach ($request->file('document_attachments') as $file) {
                $documentPaths[] = [
                    'path' => $file->store('achievements/documents', 'public'),
                    'original_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                ];
            }
        }
        
        // Convert array to JSON string for database storage
        $validated['document_attachments'] = !empty($documentPaths) ? $documentPaths : null;

        $achievement->update($validated);

        return redirect()->back()->with('success', 'Achievement updated successfully');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Achievement $achievement)
    {
        $achievement->delete();
        
        return redirect()->route('admin.achievements.index')
            ->with('success', 'Achievement deleted successfully.');
    }

    public function archive(Achievement $achievement)
    {
        $achievement->update(['status' => 'archived']);

        return redirect()->route('admin.achievements.index')
            ->with('success', 'Achievement archived successfully.');
    }

    public function publish(Achievement $achievement)
    {
        $achievement->update(['status' => 'published']);

        return redirect()->route('admin.achievements.index')
            ->with('success', 'Achievement published successfully.');
    }

    public function showInClient(Request $request) {
        $sort = $request->input('sort', 'desc');

        $achievements = Achievement::with(['galleries', 'location', 'category'])->orderBy('created_at', $sort)->paginate(5);

        return Inertia::render('Achievements', [
            'achievements' => $achievements,
            'sort' => $sort
        ]);
    }
}