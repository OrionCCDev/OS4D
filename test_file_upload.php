<?php
// Temporary test script for file upload debugging
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Http\Request;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing file upload configuration...\n";
echo "================================\n\n";

// Check if temp directory is writable
$uploadTmpDir = ini_get('upload_tmp_dir') ?: sys_get_temp_dir();
echo "Upload temp directory: {$uploadTmpDir}\n";
echo "Is writable: " . (is_writable($uploadTmpDir) ? 'YES' : 'NO') . "\n\n";

// Check project 11
$project = \App\Models\Project::find(11);
if ($project) {
    echo "Project found: {$project->name}\n";
    $slug = Str::slug($project->name);
    echo "Expected folder: public/projectsofus/11-{$slug}/\n";

    $folderPath = public_path("projectsofus/11-{$slug}/");
    echo "Full path: {$folderPath}\n";
    echo "Exists: " . (file_exists($folderPath) ? 'YES' : 'NO') . "\n";
    echo "Is writable: " . (is_writable($folderPath) ? 'YES' : 'NO') . "\n\n";
} else {
    echo "Project 11 not found!\n\n";
}

// Check storage link
$storageLink = public_path('storage');
echo "Storage symlink exists: " . (file_exists($storageLink) ? 'YES' : 'NO') . "\n";
echo "Storage symlink is link: " . (is_link($storageLink) ? 'YES' : 'NO') . "\n\n";

// Test creating a file
$testFile = public_path("projectsofus/test_upload_" . time() . ".txt");
file_put_contents($testFile, "Test upload\n");
echo "Test file created: {$testFile}\n";
echo "Test file exists: " . (file_exists($testFile) ? 'YES' : 'NO') . "\n";
unlink($testFile);
echo "Test file deleted\n";

