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

class AnnouncementController extends Controller
{
    public function index() {
        $announcementCategories = AnnouncementCategory::all();
        $audiences = Audience::all();
        // $activePuroks = Sitio::where('is_available', 1)->get();
        $offices = Department::all();
        $documents = Document::all();

        return Inertia::render('Admin/CreateAnnouncements', [
            'announcementCategories' => $announcementCategories,
            'audiences' => $audiences,
            // 'activePuroks' => $activePuroks,
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
            'level' => 'required|in:high,medium,low',
            'is_pinned' => 'required|in:yes,no',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'publish_at' => 'required|date',
            'event_date' => 'required|date',
            'venue' => 'required|string|max:255',
            'expiry_date' => 'required|date|after:publish_at',
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
                'publish_at' => $validated['publish_at'],
                'event_date' => $validated['event_date'],
                'venue' => $validated['venue'],
                'expiry_date' => $validated['expiry_date'],
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
        $announcements = Announcement::with(['attachments', 'category', 'documents', 'departments', 'audiences'])->get();

        // Separate the pinned reports with non-pinned reports
        $grouped = [
            'pinned' => $announcements->where('is_pinned', 1)->values(),
            'regular' => $announcements->where('is_pinned', 0)->values(),
        ];

        return Inertia::render('Admin/Announcements', [
            'announcements' => $grouped,
        ]);
    }
}
