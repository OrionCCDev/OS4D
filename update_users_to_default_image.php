<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== Update Users to Default Profile Image ===\n\n";

// Find all users without a custom image or with old default images
$usersToUpdate = User::whereNull('img')
    ->orWhere('img', '')
    ->orWhere('img', '1.png')
    ->orWhere('img', 'default.jpg')
    ->orWhere('img', 'default_user.jpg')
    ->orWhere('img', 'default-user.jpg')
    ->get();

echo "Found " . $usersToUpdate->count() . " users to update\n\n";

$updated = 0;
foreach ($usersToUpdate as $user) {
    $oldImage = $user->img ?: 'null';
    $user->img = 'default.png';
    $user->save();

    echo "✓ Updated user: {$user->name} ({$user->email}) - Old image: {$oldImage} → default.png\n";
    $updated++;
}

echo "\n=== Update Complete ===\n";
echo "Updated {$updated} users to use default.png\n";
echo "\nAll users now have a valid profile image!\n";
echo "Remember to upload your default.png file to: public/uploads/users/default.png\n";
