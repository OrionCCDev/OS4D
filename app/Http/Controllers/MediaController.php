<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function upload(Request $request)
    {
        // Support both single and multiple file uploads
        $validated = $request->validate([
            'file' => ['nullable', 'file', 'max:20480'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'max:20480'],
            'folder' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $folder = trim($validated['folder'] ?? '', '/');

        // Create directory if it doesn't exist
        $uploadDir = public_path("uploads/media" . ($folder ? "/{$folder}" : ''));
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploadedFiles = [];
        $files = [];

        // Handle multiple files
        if ($request->hasFile('files')) {
            $files = $request->file('files');
        }
        // Handle single file (backward compatibility)
        elseif ($request->hasFile('file')) {
            $files = [$request->file('file')];
        }

        if (empty($files)) {
            return response()->json(['ok' => false, 'error' => 'No files provided'], 400);
        }

        foreach ($files as $file) {
            // Generate unique filename
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . uniqid() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
            $filePath = "uploads/media" . ($folder ? "/{$folder}" : '') . "/{$filename}";

            // Move file to public directory
            $file->move($uploadDir, $filename);

            $uploadedFiles[] = [
                'path' => $filePath,
                'url' => url($filePath),
                'original_name' => $originalName,
            ];
        }

        // Return single file format for backward compatibility
        if (count($uploadedFiles) === 1) {
            return response()->json([
                'ok' => true,
                'path' => $uploadedFiles[0]['path'],
                'url' => $uploadedFiles[0]['url']
            ]);
        }

        // Return multiple files format
        return response()->json([
            'ok' => true,
            'files' => $uploadedFiles,
            'count' => count($uploadedFiles)
        ]);
    }

    public function list(Request $request)
    {
        $folder = trim($request->query('folder', ''), '/');
        $basePath = public_path("uploads/media" . ($folder ? "/{$folder}" : ''));

        $files = [];
        $directories = [];

        if (file_exists($basePath)) {
            $items = scandir($basePath);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;

                $itemPath = $basePath . '/' . $item;
                $relativePath = "uploads/media" . ($folder ? "/{$folder}" : '') . "/{$item}";

                if (is_file($itemPath)) {
                    $files[] = [
                        'name' => $item,
                        'path' => $relativePath,
                        'url' => url($relativePath),
                    ];
                } elseif (is_dir($itemPath)) {
                    $directories[] = $relativePath;
                }
            }
        }

        return response()->json(['folders' => $directories, 'files' => $files]);
    }

    public function makeFolder(Request $request)
    {
        $validated = $request->validate([
            'folder' => ['required', 'string']
        ]);
        $folder = public_path('uploads/media/' . trim($validated['folder'], '/'));
        if (!file_exists($folder)) {
            mkdir($folder, 0755, true);
        }
        return response()->json(['ok' => true]);
    }
}


