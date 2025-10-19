<?php

/**
 * Check and fix uploads directory permissions
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Uploads Directory Permissions ===\n\n";

$uploadsDir = public_path('uploads/users');

// Check if directory exists
if (!file_exists($uploadsDir)) {
    echo "❌ Directory does not exist: $uploadsDir\n";
    echo "Creating directory...\n";
    if (mkdir($uploadsDir, 0755, true)) {
        echo "✅ Directory created successfully\n";
    } else {
        echo "❌ Failed to create directory\n";
        exit(1);
    }
} else {
    echo "✅ Directory exists: $uploadsDir\n";
}

// Check permissions
$perms = fileperms($uploadsDir);
$permsOctal = substr(sprintf('%o', $perms), -4);
echo "Current permissions: $permsOctal\n";

// Check if writable
if (is_writable($uploadsDir)) {
    echo "✅ Directory is writable\n";
} else {
    echo "❌ Directory is NOT writable\n";
    echo "Attempting to fix permissions...\n";
    if (chmod($uploadsDir, 0755)) {
        echo "✅ Permissions fixed\n";
    } else {
        echo "❌ Failed to fix permissions. Please manually run: chmod 755 $uploadsDir\n";
    }
}

// List existing files
echo "\n=== Existing User Images ===\n";
$files = glob($uploadsDir . '/*');
if (count($files) > 0) {
    foreach ($files as $file) {
        $filename = basename($file);
        $size = filesize($file);
        $readable = is_readable($file) ? '✅' : '❌';
        echo "$readable $filename (" . number_format($size / 1024, 2) . " KB)\n";
    }
} else {
    echo "No files found\n";
}

// Check if default.png exists
echo "\n=== Default Image Check ===\n";
$defaultImage = $uploadsDir . '/default.png';
if (file_exists($defaultImage)) {
    echo "✅ default.png exists\n";
    $size = filesize($defaultImage);
    echo "   Size: " . number_format($size / 1024, 2) . " KB\n";
} else {
    echo "❌ default.png does NOT exist\n";
    echo "   Please ensure you have a default.png in: $uploadsDir\n";
}

echo "\n=== Test File Creation ===\n";
$testFile = $uploadsDir . '/test_write_' . time() . '.txt';
if (file_put_contents($testFile, 'test')) {
    echo "✅ Can write files to directory\n";
    unlink($testFile);
    echo "✅ Can delete files from directory\n";
} else {
    echo "❌ Cannot write files to directory\n";
    echo "   This may cause image uploads to fail\n";
}

echo "\n=== Summary ===\n";
echo "Directory: $uploadsDir\n";
echo "Writable: " . (is_writable($uploadsDir) ? 'YES' : 'NO') . "\n";
echo "Permissions: $permsOctal\n";
echo "\nIf uploads are still failing, check:\n";
echo "1. PHP upload_max_filesize setting\n";
echo "2. PHP post_max_size setting\n";
echo "3. Server disk space\n";
echo "4. SELinux or AppArmor restrictions\n";

