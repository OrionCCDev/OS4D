<?php

namespace App\Http\Controllers;

use App\Models\ProjectFolderFile;
use App\Models\Project;
use App\Models\ProjectFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProjectFolderFileController extends Controller
{
    /**
     * Display files in a folder
     */
    public function index(Request $request, Project $project)
    {
        $folderId = $request->query('folder');

        // Get files in the specified folder or ALL files in the project
        $query = ProjectFolderFile::where('project_id', $project->id)
            ->with(['uploader', 'folder'])
            ->latest();

        if ($folderId) {
            // If folder is specified, only get files in that folder
            $query->where('folder_id', $folderId);
        }
        // If no folder specified, get ALL files from ALL folders in the project

        $files = $query->get()->map(function($file) {
            return [
                'id' => $file->id,
                'original_name' => $file->original_name,
                'display_name' => $file->display_name,
                'description' => $file->description,
                'mime_type' => $file->mime_type,
                'size_bytes' => $file->size_bytes,
                'human_readable_size' => $file->human_readable_size,
                'url' => $file->url,
                'uploaded_by' => $file->uploaded_by,
                'folder_id' => $file->folder_id,
                'uploader' => $file->uploader ? ['name' => $file->uploader->name] : null,
                'folder' => $file->folder ? ['name' => $file->folder->name] : null,
                'created_at' => $file->created_at ? $file->created_at->toDateTimeString() : null,
            ];
        })->values()->toArray();

        return response()->json($files);
    }

    /**
     * Upload a file to a project folder
     */
    public function store(Request $request, Project $project)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:102400', // 100MB max
                'folder_id' => 'nullable|exists:project_folders,id',
                'display_name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
            ]);

            // Check if user is a manager
            if (!Auth::user()->isManager()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Only managers can upload files'
                ], 403);
            }

            $file = $request->file('file');

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'error' => 'No file provided'
                ], 400);
            }

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

        // Store the file using direct copy (most reliable)
        $tmpPath = $file->getPathname();

        if (!file_exists($tmpPath)) {
            throw new \Exception('Temporary file not found: ' . $tmpPath);
        }

        $relativePath = $folderPath . $filename;
        $fullFilePath = public_path($relativePath);

        // Ensure directory exists
        $directory = dirname($fullFilePath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Direct copy (most reliable method)
        if (!copy($tmpPath, $fullFilePath)) {
            throw new \Exception('Failed to copy file to: ' . $fullFilePath);
        }

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

        // Reload with relationships
        $fileRecord = ProjectFolderFile::with(['uploader', 'folder'])->find($fileRecord->id);

        return response()->json([
            'success' => true,
            'file' => $fileRecord->toArray(),
            'message' => 'File uploaded successfully'
        ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('File upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
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
