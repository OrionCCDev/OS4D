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
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:20480'],
            'folder' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $folder = trim($validated['folder'] ?? '', '/');

        // Create directory if it doesn't exist
        $uploadDir = public_path("uploads/media" . ($folder ? "/{$folder}" : ''));
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $originalName = $request->file('file')->getClientOriginalName();
        $extension = $request->file('file')->getClientOriginalExtension();
        $filename = time() . '_' . uniqid() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
        $filePath = "uploads/media" . ($folder ? "/{$folder}" : '') . "/{$filename}";
        $fullPath = public_path($filePath);

        // Move file to public directory
        $request->file('file')->move($uploadDir, $filename);

        return response()->json(['ok' => true, 'path' => $filePath, 'url' => url($filePath)]);
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


