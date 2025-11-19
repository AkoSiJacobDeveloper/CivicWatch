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

    public function index() {
        $issue_type = IssueType::withCount('reports')
            ->orderBy('name')
            ->paginate(10);

        return Inertia::render('Admin/Issues', [
            'issue_type' => $issue_type
        ]);
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255|unique:issue_types,name',
            'priority_level' => 'required|in:high,medium,low',
        ]);

        IssueType::create([
            'name' => $request->name,
            'active' => true,
            'priority_level' => $request->priority_level,
        ]);

        return back()->with('success', 'Type of Issue created successfully!');
    }

    public function update(Request $request, IssueType $issueType) {
        $request->validate([
            'name' => 'required|string|max:255|unique:issue_types,name,' . $issueType->id,
            'priority_level' => 'required|in:high,medium,low',
            'active' => 'boolean',
        ]);

        $issueType->update($request->only(['name', 'priority_level', 'active']));
        return back()->with('success', 'Type of Issue updated successfully!');
    }

    public function deactivate(IssueType $issueType) {
        $issueType->update(['active' => false]);
        return back()->with('success', 'Type of issue deactivated successfully!');
    }
}
