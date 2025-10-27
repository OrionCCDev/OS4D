<?php
// Test the actual API endpoint
// Run: php test_api_endpoint.php

$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['folder'] = '';

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;

echo "=== Testing API Endpoint ===\n\n";

// Test with folder_id = NULL (root level)
$project11 = \App\Models\Project::find(11);
if (!$project11) {
    echo "Project 11 not found\n";
    exit(1);
}

echo "Project: {$project11->name}\n";
echo "Testing root level files (folder_id = NULL)\n\n";

$query = \App\Models\ProjectFolderFile::where('project_id', 11)
    ->whereNull('folder_id')
    ->with(['uploader', 'folder'])
    ->latest();

$files = $query->get();

echo "Found {$files->count()} files\n\n";

$filesArray = $files->map(function($file) {
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

echo "JSON Response:\n";
$json = json_encode($filesArray, JSON_PRETTY_PRINT);
echo $json . "\n\n";

// Validate JSON
$decoded = json_decode($json);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "✓ Valid JSON\n";
} else {
    echo "✗ Invalid JSON: " . json_last_error_msg() . "\n";
}

