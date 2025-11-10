<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectFolderController extends Controller
{
    public function index()
    {
        $folders = ProjectFolder::with('project', 'parent')->latest()->paginate(15);
        return view('folders.index', compact('folders'));
    }

    public function create()
    {
        $projects = Project::orderBy('name')->get();
        $folders = ProjectFolder::orderBy('name')->get();
        $selectedProjectId = request()->query('project_id');
        $selectedParentId = request()->query('parent_id');
        return view('folders.create', compact('projects', 'folders', 'selectedProjectId', 'selectedParentId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'parent_id' => 'nullable|exists:project_folders,id',
            'name' => 'required|string|max:255',
        ]);
        $folder = ProjectFolder::create($validated);

        // Create matching directory in public: public/projectsofus/{project-id}-{slug}/{nested...}
        $path = $this->buildFolderPath($folder);
        $fullPath = public_path($path);
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        // Redirect to the project folder view instead of folders index
        return redirect()->route('projects.show', [
            'project' => $folder->project_id,
            'folder' => $folder->parent_id
        ])->with('success', 'Folder created');
    }

    public function edit(ProjectFolder $folder)
    {
        $projects = Project::orderBy('name')->get();
        $folders = ProjectFolder::where('id', '!=', $folder->id)->orderBy('name')->get();
        return view('folders.edit', compact('folder', 'projects', 'folders'));
    }

    public function update(Request $request, ProjectFolder $folder)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:project_folders,id',
            'name' => 'required|string|max:255',
        ]);
        $folder->update($validated);

        // Redirect to the project folder view instead of folders index
        return redirect()->route('projects.show', [
            'project' => $folder->project_id,
            'folder' => $folder->parent_id
        ])->with('success', 'Folder updated');
    }

    public function destroy(ProjectFolder $folder)
    {
        // Store project_id and parent_id before deletion
        $projectId = $folder->project_id;
        $parentId = $folder->parent_id;

        if (!Auth::user()->canDelete()) {
            return redirect()->route('projects.show', [
                'project' => $projectId,
                'folder' => $parentId
            ])->with('error', 'You do not have permission to delete folders.');
        }

        // Remove physical directory for this folder (and its subfolders)
        try {
            $path = $this->buildFolderPath($folder);
            $fullPath = public_path($path);
            if (file_exists($fullPath)) {
                $this->deleteDirectory($fullPath);
            }
        } catch (\Throwable $e) {
            // ignore fs errors
        }
        $folder->delete();

        // Redirect to the project folder view instead of folders index
        return redirect()->route('projects.show', [
            'project' => $projectId,
            'folder' => $parentId
        ])->with('success', 'Folder deleted');
    }

    private function buildFolderPath(ProjectFolder $folder): string
    {
        $projectSlug = Str::slug($folder->project->name);
        $segments = [];
        $current = $folder;
        while ($current) {
            array_unshift($segments, Str::slug($current->name));
            $current = $current->parent;
        }
        $relative = implode('/', $segments);
        return 'projectsofus/' . $folder->project_id . '-' . $projectSlug . '/' . $relative;
    }

    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}


