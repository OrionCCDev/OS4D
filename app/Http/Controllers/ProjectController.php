<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\ProjectFolder;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::latest()->paginate(12);
        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $projectManagers = ProjectManager::orderBy('name')->get();
        return view('projects.create', compact('projectManagers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_code' => 'nullable|string|max:12',
            'description' => 'nullable|string',
            'status' => 'nullable|in:draft,active,on_hold,completed,cancelled',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'project_manager_id' => 'nullable|exists:project_managers,id',
        ]);

        $validated['owner_id'] = Auth::id();
        $project = Project::create($validated);

        // Create a folder for the project: public/projectsofus/{id}-{slug}
        $slug = Str::slug($project->name);
        $path = 'projectsofus/' . $project->id . '-' . $slug;
        $fullPath = public_path($path);
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        // Note: No automatic root folder creation - users can create folders as needed
        return redirect()->route('projects.index')->with('success', 'Project created');
    }

    public function edit(Project $project)
    {
        $projectManagers = ProjectManager::orderBy('name')->get();
        return view('projects.edit', compact('project', 'projectManagers'));
    }

    public function show(Project $project)
    {
        // Build full folder tree for the project and support selecting a folder
        $selectedFolderId = request()->query('folder');

        // Fetch all folders for this project with task counts
        $allFolders = ProjectFolder::where('project_id', $project->id)
            ->withCount(['tasks', 'children'])
            ->orderBy('name')
            ->get();

        // Add task counts for each folder (including incomplete tasks)
        $allFolders->each(function ($folder) {
            $folder->incomplete_tasks_count = $folder->tasks()->where('status', '!=', 'done')->count();
        });

        // Group children by parent_id
        $childrenByParent = [];
        foreach ($allFolders as $f) {
            $parentId = $f->parent_id ?: 0; // use 0 to represent root
            if (!isset($childrenByParent[$parentId])) {
                $childrenByParent[$parentId] = [];
            }
            $childrenByParent[$parentId][] = $f;
        }

        // Attach children relation recursively for display
        $attachChildren = function (ProjectFolder $node) use (&$attachChildren, &$childrenByParent) {
            $childList = collect($childrenByParent[$node->id] ?? []);
            $childList->each(function ($c) use (&$attachChildren) {
                $attachChildren($c);
            });
            $node->setRelation('children', $childList);
        };

        // Root folders (parent_id null)
        $rootFolders = collect($childrenByParent[0] ?? []);
        $rootFolders->each(function ($root) use (&$attachChildren) {
            $attachChildren($root);
        });

        // Determine selected folder (if provided and belongs to this project)
        $selectedFolder = null;
        $expandedFolderIds = [];
        $breadcrumbs = [];
        if ($selectedFolderId) {
            $selectedFolder = $allFolders->firstWhere('id', (int) $selectedFolderId);
            if (!$selectedFolder) {
                $selectedFolderId = null;
            } else {
                // Build breadcrumbs for the selected folder
                $current = $selectedFolder;
                while ($current) {
                    $breadcrumbs[] = $current;
                    $current = $allFolders->firstWhere('id', $current->parent_id);
                }
                $breadcrumbs = array_reverse($breadcrumbs);
            }
        }

        // Compute ancestor chain for auto-expansion in the tree
        if ($selectedFolder) {
            $current = $selectedFolder;
            while ($current) {
                $expandedFolderIds[] = (int) $current->id;
                $current = $allFolders->firstWhere('id', (int) $current->parent_id);
            }
        }

        // Collect descendant folder ids of the selected folder for task filtering
        $descendantFolderIds = [];
        if ($selectedFolder) {
            $stack = [(int) $selectedFolder->id];
            while (!empty($stack)) {
                $currentId = array_pop($stack);
                $descendantFolderIds[] = $currentId;
                $children = $childrenByParent[$currentId] ?? [];
                foreach ($children as $child) {
                    $stack[] = (int) $child->id;
                }
            }
        }

        // Load tasks: if a folder is selected, include tasks in that folder and all descendants
        $tasksQuery = $project->tasks()->with(['folder', 'creator', 'assignee'])->latest();
        if (!empty($descendantFolderIds)) {
            $tasksQuery->whereIn('folder_id', $descendantFolderIds);
        }
        $tasks = $tasksQuery->paginate(20);

        return view('projects.show', compact('project', 'rootFolders', 'selectedFolder', 'tasks', 'descendantFolderIds', 'expandedFolderIds', 'allFolders', 'breadcrumbs'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_code' => 'nullable|string|max:12',
            'description' => 'nullable|string',
            'status' => 'nullable|in:draft,active,on_hold,completed,cancelled',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'project_manager_id' => 'nullable|exists:project_managers,id',
        ]);

        $project->update($validated);
        return redirect()->route('projects.index')->with('success', 'Project updated');
    }

    public function destroy(Project $project)
    {
        try {
            // Get project details for logging
            $projectName = $project->name;
            $projectId = $project->id;

            // Count related data before deletion
            $tasksCount = $project->tasks()->count();
            $foldersCount = $project->folders()->count();
            $teamMembersCount = $project->users()->count();

            // Delete all tasks associated with this project
            $project->tasks()->delete();

            // Delete all folders associated with this project
            $project->folders()->delete();

            // Remove all team members from the project
            $project->users()->detach();

            // Delete the project's physical directory
            $this->deleteProjectDirectory($project);

            // Finally, delete the project itself
            $project->delete();

            return redirect()->route('projects.index')->with('success',
                "Project '{$projectName}' and all its associated data ({$tasksCount} tasks, {$foldersCount} folders, {$teamMembersCount} team members) have been successfully deleted."
            );

        } catch (\Exception $e) {
            Log::error('Failed to delete project: ' . $e->getMessage(), [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error',
                'Failed to delete project. Please try again or contact support if the problem persists.'
            );
        }
    }

    /**
     * Delete the project's physical directory
     */
    private function deleteProjectDirectory(Project $project)
    {
        try {
            $slug = Str::slug($project->name);
            $path = 'projectsofus/' . $project->id . '-' . $slug;
            $fullPath = public_path($path);

            if (file_exists($fullPath)) {
                $this->deleteDirectory($fullPath);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to delete project directory: ' . $e->getMessage(), [
                'project_id' => $project->id,
                'path' => $path ?? 'unknown'
            ]);
        }
    }

    /**
     * Recursively delete a directory and all its contents
     */
    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}


