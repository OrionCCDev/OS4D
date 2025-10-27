<?php

namespace App\Http\Controllers;

use App\Models\ProjectFolderFile;
use App\Models\Project;
use App\Models\ProjectFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectFolderFileController extends Controller
{
    /**
     * Display files in a folder
     */
    public function index(Request $request, Project $project)
    {
        $folderId = $request->query('folder');

        // Get files in the specified folder or project root
        $query = ProjectFolderFile::where('project_id', $project->id)
            ->with(['uploader', 'folder'])
            ->latest();

        if ($folderId) {
            $query->where('folder_id', $folderId);
        } else {
            $query->whereNull('folder_id');
        }

        $files = $query->get();

        return response()->json($files);
    }

    /**
     * Upload a file to a project folder
     */
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'file' => 'required|file|max:102400', // 100MB max
            'folder_id' => 'nullable|exists:project_folders,id',
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Check if user is a manager
        if (!Auth::user()->isManager()) {
            return response()->json(['error' => 'Only managers can upload files'], 403);
        }

        $file = $request->file('file');

        // Build the storage path based on folder hierarchy
        $folder = null;
        $folderPath = '';
        if ($request->folder_id) {
            $folder = ProjectFolder::find($request->folder_id);
            if ($folder && $folder->project_id === $project->id) {
                $folderPath = $this->buildFolderPath($folder);
            }
        } else {
            // Root level: public/projectsofus/{project-id}-{slug}/
            $projectSlug = Str::slug($project->name);
            $folderPath = 'projectsofus/' . $project->id . '-' . $projectSlug . '/';
        }

        // Create directory if it doesn't exist
        $fullPath = public_path($folderPath);
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        // Generate unique filename
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '_' . uniqid() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;

        // Move file to the directory
        $relativePath = $folderPath . $filename;
        $fullFilePath = public_path($relativePath);

        // Ensure the directory exists
        $directory = dirname($fullFilePath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $file->move($directory, $filename);

        // Save file record
        $fileRecord = ProjectFolderFile::create([
            'project_id' => $project->id,
            'folder_id' => $request->folder_id,
            'uploaded_by' => Auth::id(),
            'original_name' => $originalName,
            'display_name' => $request->display_name ?? $originalName,
            'description' => $request->description,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'disk' => 'public',
            'path' => $relativePath,
        ]);

        return response()->json([
            'success' => true,
            'file' => $fileRecord->load('uploader', 'folder'),
            'message' => 'File uploaded successfully'
        ]);
    }

    /**
     * Update file metadata
     */
    public function update(Request $request, Project $project, ProjectFolderFile $file)
    {
        // Verify file belongs to project
        if ($file->project_id !== $project->id) {
            return response()->json(['error' => 'File not found in this project'], 404);
        }

        // Check if user is a manager
        if (!Auth::user()->isManager()) {
            return response()->json(['error' => 'Only managers can edit files'], 403);
        }

        $request->validate([
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $file->update([
            'display_name' => $request->display_name ?? $file->display_name,
            'description' => $request->description ?? $file->description,
        ]);

        return response()->json([
            'success' => true,
            'file' => $file->load('uploader', 'folder'),
            'message' => 'File updated successfully'
        ]);
    }

    /**
     * Delete a file
     */
    public function destroy(Project $project, ProjectFolderFile $file)
    {
        // Verify file belongs to project
        if ($file->project_id !== $project->id) {
            return response()->json(['error' => 'File not found in this project'], 404);
        }

        // Check if user is a manager
        if (!Auth::user()->isManager()) {
            return response()->json(['error' => 'Only managers can delete files'], 403);
        }

        // Delete physical file
        $fullPath = public_path($file->path);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        // Delete record
        $file->delete();

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully'
        ]);
    }

    /**
     * Download a file
     */
    public function download(Project $project, ProjectFolderFile $file)
    {
        // Verify file belongs to project
        if ($file->project_id !== $project->id) {
            abort(404, 'File not found');
        }

        $fullPath = public_path($file->path);

        if (!file_exists($fullPath)) {
            abort(404, 'File not found on disk');
        }

        return response()->download($fullPath, $file->display_name ?? $file->original_name);
    }

    /**
     * Build folder path recursively
     */
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
        return 'projectsofus/' . $folder->project_id . '-' . $projectSlug . '/' . $relative . '/';
    }
}
