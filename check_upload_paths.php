<?php
// Simple diagnostic script - run from cPanel terminal
// Usage: php check_upload_paths.php

echo "=== File Upload Diagnostics ===\n\n";

// Check project 11 exists
$db = new mysqli('localhost', 'edlb2bdo7yna_odc', 'DB_PASSWORD_HERE', 'edlb2bdo7yna_odc');
if ($db->connect_error) {
    echo "Database connection failed: " . $db->connect_error . "\n";
    exit(1);
}

$result = $db->query("SELECT id, name FROM projects WHERE id = 11 LIMIT 1");
if ($row = $result->fetch_assoc()) {
    echo "Project 11 found: {$row['name']}\n";
    $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $row['name']));
    echo "Slug: {$slug}\n";
    echo "Expected path: public/projectsofus/11-{$slug}/\n";

    $checkPath = "public/projectsofus/11-{$slug}/";
    if (file_exists($checkPath)) {
        echo "Folder exists: YES\n";
        echo "Is writable: " . (is_writable($checkPath) ? "YES" : "NO") . "\n";
    } else {
        echo "Folder exists: NO\n";
        echo "Creating folder...\n";
        if (mkdir($checkPath, 0755, true)) {
            echo "Folder created successfully\n";
        } else {
            echo "Failed to create folder\n";
        }
    }
} else {
    echo "Project 11 not found in database\n";
    echo "Available projects:\n";
    $allProjects = $db->query("SELECT id, name FROM projects ORDER BY id");
    while ($p = $allProjects->fetch_assoc()) {
        echo "  {$p['id']} - {$p['name']}\n";
    }
}

$db->close();

echo "\n=== Checking file upload config ===\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "upload_tmp_dir: " . (ini_get('upload_tmp_dir') ?: 'default system temp') . "\n";
echo "temp directory writable: " . (is_writable(ini_get('upload_tmp_dir') ?: sys_get_temp_dir()) ? "YES" : "NO") . "\n";

