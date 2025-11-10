<?php

namespace App\Http\Controllers;

use App\Models\ProjectManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projectManagers = ProjectManager::withCount('projects')->latest()->paginate(15);
        return view('project-managers.index', compact('projectManagers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('project-managers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'orion_id' => 'nullable|string|max:255|unique:project_managers,orion_id',
            'email' => 'nullable|email|unique:project_managers,email',
            'mobile' => 'nullable|string|max:50',
        ]);

        ProjectManager::create($validated);
        return redirect()->route('project-managers.index')->with('success', 'Project Manager created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProjectManager $project_manager)
    {
        $project_manager->load('projects');
        return view('project-managers.show', compact('project_manager'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProjectManager $project_manager)
    {
        return view('project-managers.edit', compact('project_manager'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProjectManager $project_manager)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'orion_id' => 'nullable|string|max:255|unique:project_managers,orion_id,' . $project_manager->id,
            'email' => 'nullable|email|unique:project_managers,email,' . $project_manager->id,
            'mobile' => 'nullable|string|max:50',
        ]);

        $project_manager->update($validated);
        return redirect()->route('project-managers.index')->with('success', 'Project Manager updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectManager $project_manager)
    {
        if (!Auth::user()->canDelete()) {
            return redirect()->route('project-managers.index')->with('error', 'You do not have permission to delete project managers.');
        }

        // Check if manager has projects
        if ($project_manager->projects()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete Project Manager with assigned projects. Please reassign projects first.');
        }

        $project_manager->delete();
        return redirect()->route('project-managers.index')->with('success', 'Project Manager deleted successfully');
    }
}
