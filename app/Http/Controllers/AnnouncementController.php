<?php

namespace App\Http\Controllers;

use App\Models\AnnouncementCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Audience;
use App\Models\Sitio;
use App\Models\Department;
use App\Models\Announcement;
use App\Models\Document;
use App\Models\Attachment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AnnouncementController extends Controller
{
    public function index() {
        $announcementCategories = AnnouncementCategory::all();
        $audiences = Audience::all();
        $offices = Department::all();
        $documents = Document::all();

        return Inertia::render('Admin/CreateAnnouncements', [
            'announcementCategories' => $announcementCategories,
            'audiences' => $audiences,
            'offices' => $offices,
            'documents' => $documents
        ]);
    }

    public function create() {
        
    }

    public function store(Request $request) {
        Log::info('Creating new announcement');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|exists:announcement_categories,id',
            'level' => 'required|in:urgent,important,general',
            'is_pinned' => 'required|in:yes,no',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'publish_at' => 'nullable|date',
            'event_date' => 'required|date',
            'venue' => 'required|string|max:255',
            'expiry_date' => 'nullable|date|',
            'contact_person' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'purok' => 'required|in:all,specific',
            'audiences' => 'required|array',
            'audiences.*' => 'exists:audiences,id',
            'departments' => 'required|array',
            'departments.*' => 'exists:departments,id',
            'instructions' => 'nullable|string',
            'counts' => 'nullable|integer',
            'reg_deadline' => 'nullable|date',
            'requirements' => 'nullable|array',
            'requirements.*' => 'exists:documents,id',
            'other_document' => 'nullable|string|max:100',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,txt|max:10240',
            'specific_area' => 'nullable|string|max:255|required_if:purok,specific',
        ]);

        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('announcements/images', 'public');
            }

            // Convert is_pinned to boolean
            $validated['is_pinned'] = $validated['is_pinned'] === 'yes';

            // Create announcement
            $announcementData = [
                'title' => $validated['title'],
                'category_id' => $validated['type'],
                'level' => $validated['level'],
                'is_pinned' => $validated['is_pinned'],
                'content' => $validated['content'],
                'image' => $validated['image'] ?? null,
                'publish_at' => $validated['publish_at'] ?? null,
                'event_date' => $validated['event_date'],
                'venue' => $validated['venue'],
                'expiry_date' => $validated['expiry_date'] ?? null,
                'contact_person' => $validated['contact_person'],
                'contact_number' => $validated['contact_number'],
                'purok' => $validated['purok'],
                'instructions' => $validated['instructions'] ?? null,
                'counts' => $validated['counts'] ?? 0,
                'reg_deadline' => $validated['reg_deadline'] ?? null,
                'other_document' => $validated['other_document'] ?? null,
                'specific_area' => $validated['specific_area'] ?? null,

            ];

            $announcement = Announcement::create($announcementData);
            $announcement->load('documents');

            // Handle attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filePath = $file->store('announcements/attachments', 'public');
                    
                    Attachment::create([
                        'announcement_id' => $announcement->id,
                        'file_path' => $filePath,
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ]);
                }
            }

            // Sync many-to-many relationships
            if (isset($validated['audiences'])) {
                $announcement->audiences()->sync($validated['audiences']);
            }

            if (isset($validated['departments'])) {
                $announcement->departments()->sync($validated['departments']);
            }

            if (isset($validated['requirements'])) {
                $announcement->documents()->sync($validated['requirements']);
            }

            return redirect()->route('admin.pinned.report')
                ->with('success', 'Announcement created successfully!');

        } catch (\Exception $e) {
            Log::error('Error creating announcement: ' . $e->getMessage());
            return back()->with('error', 'Failed to create announcement: ' . $e->getMessage());
        }
    }
    
    public function showData() {
        $grouped = [
            'pinned' => Announcement::with(['attachments', 'category', 'documents', 'departments', 'audiences'])
                ->where('is_pinned', 1)
                ->where(function($query) {
                    $query->whereNull('archived_at')
                        ->orWhere('archived_at', '');
                })
                ->latest()
                ->get(),
                
            'regular' => Announcement::with(['attachments', 'category', 'documents', 'departments', 'audiences'])
                ->where('is_pinned', 0)
                ->where(function($query) {
                    $query->whereNull('archived_at')
                        ->orWhere('archived_at', '');
                })
                ->latest()
                ->get(),
                
            'archived' => Announcement::with(['attachments', 'category', 'documents', 'departments', 'audiences'])
                ->whereNotNull('archived_at')
                ->where('archived_at', '!=', '')
                ->latest('archived_at')
                ->get(),
        ];

        return Inertia::render('Admin/Announcements', [
            'announcements' => $grouped,
        ]);
    }

    public function editAnnouncement($id) {
        $announcement = Announcement::with(['category', 'attachments', 'documents', 'departments', 'audiences'])->findOrFail($id);

        // Get dropdown data
        $announcementCategories = AnnouncementCategory::all();
        $audiences = Audience::all();
        $offices = Department::all();
        $documents = Document::all();

        return Inertia::render('Admin/EditPage/EditAnnouncement', [
            'announcement' => [
                'id' => $announcement->id, 
                'title' => $announcement->title, 
                'type' => $announcement->category_id,
                'level' => $announcement->level, 
                'is_pinned' => $announcement->is_pinned ? 'yes' : 'no', // Convert boolean to string
                'content' => $announcement->content, 
                'image' => $announcement->image, 
                'publish_at' => $announcement->publish_at, 
                'event_date' => $announcement->event_date, 
                'venue' => $announcement->venue, 
                'expiry_date' => $announcement->expiry_date, 
                'contact_person' => $announcement->contact_person, 
                'contact_number' => $announcement->contact_number, 
                'purok' => $announcement->purok, 
                'specific_area' => $announcement->specific_area, 
                'requirements' => $announcement->documents->pluck('id')->toArray(), // Get IDs for multiselect
                'instructions' => $announcement->instructions, 
                'counts' => $announcement->counts, 
                'reg_deadline' => $announcement->reg_deadline, 
                'other_document' => $announcement->other_document, 
                'attachments' => $announcement->attachments,
                'category' => $announcement->category,
                'documents' => $announcement->documents,
                'departments' => $announcement->departments->pluck('id')->toArray(), // Get IDs for multiselect
                'audiences' => $announcement->audiences->pluck('id')->toArray(), // Get IDs for multiselect
            ],
            // Dropdown data
            'announcementCategories' => $announcementCategories,
            'audiences' => $audiences,
            'offices' => $offices,
            'documents' => $documents
        ]);
    }

    public function updateAnnouncement(Request $request, $id)
    {
        Log::info('Updating announcement: ' . $id);
        Log::info('Request data:', $request->all());

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|exists:announcement_categories,id',
            'level' => 'required|in:urgent,important,general',
            'is_pinned' => 'required|in:yes,no',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'publish_at' => 'nullable|date',
            'event_date' => 'required|date',
            'venue' => 'required|string|max:255',
            'expiry_date' => 'nullable|date|',
            'contact_person' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'purok' => 'required|in:all,specific',
            'audiences' => 'required|array',
            'audiences.*' => 'exists:audiences,id',
            'departments' => 'required|array',
            'departments.*' => 'exists:departments,id',
            'instructions' => 'nullable|string',
            'counts' => 'nullable|integer',
            'reg_deadline' => 'nullable|date',
            'requirements' => 'nullable|array',
            'requirements.*' => 'exists:documents,id',
            'other_document' => 'nullable|string|max:100',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,txt|max:10240',
            'existing_attachments' => 'nullable|string',
            'existing_attachments.*.id' => 'exists:attachments,id',
            'specific_area' => 'nullable|string|max:255|required_if:purok,specific',
            'removed_featured_image' => 'nullable|boolean', // Add this validation
        ]);

        try {
            $announcement = Announcement::findOrFail($id);

            // Handle image removal FIRST
            if ($request->boolean('removed_featured_image')) {
                Log::info('Removing featured image for announcement: ' . $id);
                if ($announcement->image) {
                    Storage::disk('public')->delete($announcement->image);
                    Log::info('Deleted image from storage: ' . $announcement->image);
                }
                $validated['image'] = null; // Set image to null when removed
            }
            // Handle new image upload (only if image wasn't removed)
            else if ($request->hasFile('image')) {
                Log::info('Uploading new image for announcement: ' . $id);
                // Delete old image if exists
                if ($announcement->image) {
                    Storage::disk('public')->delete($announcement->image);
                }
                $validated['image'] = $request->file('image')->store('announcements/images', 'public');
                Log::info('New image stored at: ' . $validated['image']);
            }
            // Keep existing image if no new upload and not removed
            else {
                $validated['image'] = $announcement->image;
            }

            // Handle removed existing attachments
            $existingAttachments = json_decode($request->input('existing_attachments', '[]'), true);
            $keepAttachmentIds = collect($existingAttachments)
                ->pluck('id')
                ->filter()
                ->toArray();

            $announcement->attachments()
                ->whereNotIn('id', $keepAttachmentIds)
                ->each(function($attachment) {
                    Storage::disk('public')->delete($attachment->file_path);
                    $attachment->delete();
                });

            // Convert is_pinned to boolean
            $validated['is_pinned'] = $validated['is_pinned'] === 'yes';

            // Update announcement
            $announcementData = [
                'title' => $validated['title'],
                'category_id' => $validated['type'],
                'level' => $validated['level'],
                'is_pinned' => $validated['is_pinned'],
                'content' => $validated['content'],
                'image' => $validated['image'], // This will be null if image was removed
                'publish_at' => $validated['publish_at'] ?? null,
                'event_date' => $validated['event_date'],
                'venue' => $validated['venue'],
                'expiry_date' => $validated['expiry_date'] ?? null,
                'contact_person' => $validated['contact_person'],
                'contact_number' => $validated['contact_number'],
                'purok' => $validated['purok'],
                'instructions' => $validated['instructions'] ?? null,
                'counts' => $validated['counts'] ?? 0,
                'reg_deadline' => $validated['reg_deadline'] ?? null,
                'other_document' => $validated['other_document'] ?? null,
                'specific_area' => $validated['specific_area'] ?? null,
            ];

            $announcement->update($announcementData);

            // Handle new attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filePath = $file->store('announcements/attachments', 'public');
                    
                    Attachment::create([
                        'announcement_id' => $announcement->id,
                        'file_path' => $filePath,
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ]);
                }
            }

            // Sync relationships
            if (isset($validated['audiences'])) {
                $announcement->audiences()->sync($validated['audiences']);
            }

            if (isset($validated['departments'])) {
                $announcement->departments()->sync($validated['departments']);
            }

            if (isset($validated['requirements'])) {
                $announcement->documents()->sync($validated['requirements']);
            }

            Log::info('Announcement updated successfully: ' . $id);
            Log::info('Final image value: ' . $announcement->fresh()->image);

            return redirect()->route('admin.pinned.report')
                ->with('success', 'Announcement updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating announcement: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Failed to update announcement: ' . $e->getMessage()]);
        }
    }

    public function showInClient(Request $request) {
        $sort = $request->input('sort', 'desc');

        $announcements = Announcement::with(['attachments', 'category', 'documents', 'departments', 'audiences'])
            ->where(function($query) {
                $query->whereNull('archived_at')
                    ->orWhere('archived_at', '');
            })
            ->orderBy('created_at', $sort)
            ->paginate(5);

        return Inertia::render('Announcement', [
            'announcements' => $announcements,
            'sort' => $sort,
        ]);
    }

    public function destroy($id) {  
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();

        return redirect()->back()->with('success', 'Announcement deleted successfully!');
    }

    public function archive($id) {
        $announcement = Announcement::findOrFail($id);
        $announcement->archived_at = now();
        $announcement->save();
        
        return redirect()->back()->with('success', 'Announcement archived successfully!');
    }

    public function restore($id) {
        $announcement = Announcement::findOrFail($id);
        $announcement->archived_at = null;
        $announcement->save();
        
        return redirect()->back()->with('success', 'Announcement restored successfully!');
    }

    public function bulkArchive(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:announcements,id'
        ]);

        try {
            $count = Announcement::whereIn('id', $request->ids)
                ->update(['archived_at' => now()]);

            return redirect()->back()->with('success', "{$count} announcement(s) archived successfully!");

        } catch (\Exception $e) {
            Log::error('Bulk archive error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to archive announcements');
        }
    }

    /**
     * Bulk restore announcements (Inertia version)
     */
    public function bulkRestore(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:announcements,id'
        ]);

        try {
            $count = Announcement::whereIn('id', $request->ids)
                ->update(['archived_at' => null]);

            return redirect()->back()->with('success', "{$count} announcement(s) restored successfully!");

        } catch (\Exception $e) {
            Log::error('Bulk restore error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to restore announcements');
        }
    }

    /**
     * Bulk delete announcements (Inertia version)
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:announcements,id'
        ]);

        try {
            DB::transaction(function () use ($request) {
                $announcements = Announcement::with('attachments')
                    ->whereIn('id', $request->ids)
                    ->get();

                foreach ($announcements as $announcement) {
                    if ($announcement->image) {
                        Storage::disk('public')->delete($announcement->image);
                    }

                    foreach ($announcement->attachments as $attachment) {
                        Storage::disk('public')->delete($attachment->file_path);
                        $attachment->delete();
                    }

                    $announcement->audiences()->detach();
                    $announcement->departments()->detach();
                    $announcement->documents()->detach();

                    $announcement->delete();
                }
            });

            $count = count($request->ids);
            return redirect()->back()->with('success', "{$count} announcement(s) permanently deleted!");

        } catch (\Exception $e) {
            Log::error('Bulk delete error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete announcements');
        }
    }
}
