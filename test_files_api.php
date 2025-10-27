<?php
// Test script to check files API
// Run: php test_files_api.php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Files API ===\n\n";

// Get all projects
$projects = \App\Models\Project::all(['id', 'name']);
echo "Available Projects:\n";
foreach ($projects as $project) {
    echo "  Project {$project->id}: {$project->name}\n";
}

echo "\n";

// Get files for each project
foreach ($projects as $project) {
    echo "=== Files for Project {$project->id}: {$project->name} ===\n";

    // Test root level files
    $files = \App\Models\ProjectFolderFile::where('project_id', $project->id)
        ->whereNull('folder_id')
        ->get();

    echo "Root level files: {$files->count()}\n";
    foreach ($files as $file) {
        echo "  - {$file->display_name} (Path: {$file->path})\n";
    }

    // Test with folder
    $folders = \App\Models\ProjectFolder::where('project_id', $project->id)->get();
    if ($folders->count() > 0) {
        echo "Folders with files:\n";
        foreach ($folders as $folder) {
            $folderFiles = \App\Models\ProjectFolderFile::where('project_id', $project->id)
                ->where('folder_id', $folder->id)
                ->get();
            if ($folderFiles->count() > 0) {
                echo "  Folder: {$folder->name}\n";
                foreach ($folderFiles as $file) {
                    echo "    - {$file->display_name} (Path: {$file->path})\n";
                }
            }
        }
    }
    echo "\n";
}

echo "\n=== API Response Test ===\n";

// Simulate API call for project 11
$project11 = \App\Models\Project::find(11);
if ($project11) {
    echo "Testing API response for Project 11:\n";

    // Root level
    $rootFiles = \App\Models\ProjectFolderFile::where('project_id', 11)
        ->whereNull('folder_id')
        ->with(['uploader', 'folder'])
        ->get()
        ->map(function($file) {
            return [
                'id' => $file->id,
                'display_name' => $file->display_name,
                'url' => $file->url,
                'uploader' => $file->uploader ? ['name' => $file->uploader->name] : null,
            ];
        });

    echo "JSON Response:\n";
    echo json_encode($rootFiles, JSON_PRETTY_PRINT);
} else {
    echo "Project 11 not found\n";
}

