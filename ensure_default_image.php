<?php

// Ensure default profile image exists
$defaultImagePath = __DIR__ . '/public/uploads/users/default.png';
$uploadsDir = __DIR__ . '/public/uploads/users';

// Create uploads directory if it doesn't exist
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
    echo "Created uploads directory: $uploadsDir\n";
}

// Check if default.png exists
if (!file_exists($defaultImagePath)) {
    echo "WARNING: default.png not found at: $defaultImagePath\n";
    echo "Please upload a default profile image to: public/uploads/users/default.png\n";
    echo "Recommended: 400x400px PNG image\n";
} else {
    echo "✓ Default profile image found: $defaultImagePath\n";
}

echo "\nYou can now run: php artisan migrate:fresh --seed\n";
