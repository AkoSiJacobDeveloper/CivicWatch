<?php

namespace App\Http\Controllers;

use App\Models\IssueType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IssueController extends Controller
{
    // Endpoint
    public function getActiveIssueType()
{
    return response()->json(
        IssueType::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
    );
}

    // View all type of issue
    public function index() {
        $issue_type = IssueType::withCount('reports')
            ->orderBy('name')
            ->paginate(10);

        return Inertia::render('Admin/Issues', [
            'issue_type' => $issue_type
        ]);
    }

    // Create new type of issue
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255|unique:issue_type,name',
        ]);

        IssueType::create([
            'name' => $request->name,
            'active' => true,
        ]);

        return back()->with('success', 'Type of Issue created successfully!');
    }

    // Update the type of issue
    public function update(Request $request, IssueType $issueType) {
        $request->validate([
            'name' => 'required|string|max:255|unique:issue_type,name,' . $issueType->id,
            'active' => 'boolean',
        ]);

        $issueType->update($request->only(['name', 'active']));
        return back()->with('success', 'Type of Issue updated successfully!');
    }

    // Delete the type of issue (soft delete by setting it inactive)
    public function destroy(IssueType $issueType) {
        $issueType->update(['active' => false]);
        return back()->with('success', 'Type of issue deactivated successfully!');
    }
}
