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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AchievementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'active');
        
        $query = Achievement::with('category', 'location', 'galleries');
        
        if ($status === 'archive') {
            $achievements = $query->archived()->latest()->get();
        } else {
            $achievements = $query->active()->latest()->get();
        }

        $totalAchievements = Achievement::active()->count();
        $totalArchived = Achievement::archived()->count();
        
        return Inertia::render('Admin/Achievements', [
            'achievements' => $achievements,
            'totalAchievements' => $totalAchievements,
            'totalArchived' => $totalArchived,
            'filters' => ['status' => $status] 
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

        $slug = Str::slug($validated['title']);
        $originalSlug = $slug;
        $counter = 1;

        while (Achievement::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $validated['slug'] = $slug;
        
        $validated['status'] = 'published';

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('achievements', 'public');
        }

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
        
        $validated['document_attachments'] = !empty($documentPaths) ? $documentPaths : null;

        $achievement = Achievement::create($validated);

        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $index => $file) {
                AchievementGallery::create([
                    'achievement_id' => $achievement->id,
                    'image_path' => $file->store('achievements/gallery', 'public'),
                    'caption' => '', 
                    'order_index' => $index
                ]);
            }
        }

        return redirect()->route('admin.get.achievements')->with('success', 'Achievement recorded successfully');
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
        $achievement->load('category', 'location', 'galleries');
    
        // Get categories and locations for dropdowns
        $categories = Category::all();
        $locations = Location::all();
        
        return Inertia::render('Admin/EditPage/EditAchievements', [
            'achievement' => $achievement,
            'categories' => $categories,
            'locations' => $locations,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAchievementRequest $request, Achievement $achievement)
    {
        try {
            $data = $request->except(['document_attachments', 'gallery_images', 'existing_document_attachments', 'existing_galleries', 'removed_featured_image', 'removed_galleries', 'removed_attachments', 'keep_existing_featured_image']);
            
            if ($request->removed_featured_image && $achievement->featured_image) {
                Storage::delete($achievement->featured_image);
                $data['featured_image'] = null;
            }
            
            if ($request->hasFile('featured_image')) {
                if ($achievement->featured_image) {
                    Storage::delete($achievement->featured_image);
                }
                $data['featured_image'] = $request->file('featured_image')->store('achievements/featured', 'public');
            }
            elseif ($request->keep_existing_featured_image && $achievement->featured_image) {
                $data['featured_image'] = $achievement->featured_image;
            }
            
            $existingAttachments = json_decode($request->existing_document_attachments, true) ?? [];
            $newAttachments = [];
            
            if ($request->hasFile('document_attachments')) {
                foreach ($request->file('document_attachments') as $file) {
                    $path = $file->store('achievements/documents', 'public');
                    $newAttachments[] = [
                        'original_name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'file_size' => $file->getSize(),
                    ];
                }
            }
            
            $removedAttachments = json_decode($request->removed_attachments, true) ?? [];
            $filteredExistingAttachments = array_filter($existingAttachments, function($attachment) use ($removedAttachments) {
                return !isset($attachment['id']) || !in_array($attachment['id'], $removedAttachments);
            });
            
            $data['document_attachments'] = array_merge($filteredExistingAttachments, $newAttachments);
            
            $achievement->update($data);
            
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $galleryImage) {
                    $galleryPath = $galleryImage->store('achievements/galleries', 'public');
                    
                    AchievementGallery::create([
                        'achievement_id' => $achievement->id,
                        'image_path' => $galleryPath,
                        'caption' => $galleryImage->getClientOriginalName(),
                    ]);
                }
            }
            
            $removedGalleries = json_decode($request->removed_galleries, true) ?? [];
            if (!empty($removedGalleries)) {
                $galleriesToDelete = AchievementGallery::whereIn('id', $removedGalleries)->get();
                foreach ($galleriesToDelete as $gallery) {
                    Storage::delete($gallery->image_path);
                    $gallery->delete();
                }
            }
            
            return redirect()->route('admin.achievements.index')->with('success', 'Achievement updated successfully!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update achievement: ' . $e->getMessage());
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function showInClient(Request $request) {
        $sort = $request->input('sort', 'desc');

        $achievements = Achievement::with(['galleries', 'location', 'category'])
            ->whereNull('archived_at') 
            ->orderBy('created_at', $sort)
            ->paginate(5);

        return Inertia::render('Achievements', [
            'achievements' => $achievements,
            'sort' => $sort
        ]);
    }

    public function archive($id)
    {
        $achievement = Achievement::findOrFail($id);
        $achievement->update(['archived_at' => now()]);
        
        return redirect()->back()->with('success', 'Achievement archived successfully');
    }

    public function restore($id)
    {
        $achievement = Achievement::findOrFail($id);
        $achievement-> unarchive(); 
        
        return redirect()->back()->with('success', 'Achievement restored successfully');
    }

    public function destroy(Achievement $achievement)
    {
        $achievement-> delete();
        
        return redirect()->back()->with('success', 'Achievement deleted permanently');
    }

    public function publish(Achievement $achievement)
    {
        $achievement->update(['status' => 'published']);

        return redirect()->route('admin.achievements.index')
            ->with('success', 'Achievement published successfully.');
    }

    public function bulkArchive(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:achievements,id'
        ]);

        try {
            $count = Achievement::whereIn('id', $request->ids)
                ->update(['archived_at' => now()]);

            return redirect()->back()->with('success', "{$count} Achievement(s) archived successfully!");

        } catch (\Exception $e) {
            Log::error('Bulk archive error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to archive achievement');
        }
    }

    public function bulkRestore(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:achievements,id'
        ]);

        try {
            $count = Achievement::whereIn('id', $request->ids)
                ->update(['archived_at' => null]);

            return redirect()->back()->with('success', "{$count} Achievement(s) restored successfully!");

        } catch (\Exception $e) {
            Log::error('Bulk restore error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to restore achievement');
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:achievements,id' 
        ]);

        try {
            DB::transaction(function () use ($request) {
                $achievements = Achievement::with('galleries')
                    ->whereIn('id', $request->ids)
                    ->get();

                foreach ($achievements as $achievement) {
                    if ($achievement->featured_image) {
                        Storage::disk('public')->delete($achievement->featured_image);
                    }

                    if ($achievement->document_attachments) {
                        foreach ($achievement->document_attachments as $attachment) {
                            if (isset($attachment['path'])) {
                                Storage::disk('public')->delete($attachment['path']);
                            }
                        }
                    }

                    if ($achievement->galleries->isNotEmpty()) {
                        foreach ($achievement->galleries as $gallery) {
                            if ($gallery->image_path) {
                                Storage::disk('public')->delete($gallery->image_path);
                            }
                        }
                    }
                }

                Achievement::whereIn('id', $request->ids)->delete();
            });

            $count = count($request->ids);
            return redirect()->back()->with('success', "{$count} achievement(s) permanently deleted!");

        } catch (\Exception $e) {
            Log::error('Bulk delete failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete achievement(s)');
        }
    }

}