<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\UnifiedNotification;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking Notification System\n";
echo "============================\n\n";

// Check all users and their notification counts
$users = User::all();
foreach ($users as $user) {
    $unreadCount = UnifiedNotification::getUnreadCountForUser($user->id);
    echo "User: {$user->name} ({$user->email}) - Role: {$user->role}\n";
    echo "  Unread notifications: {$unreadCount}\n";

    if ($unreadCount > 0) {
        $latestNotifications = UnifiedNotification::forUser($user->id)
            ->unread()
            ->latest()
            ->take(3)
            ->get();

        echo "  Latest notifications:\n";
        foreach ($latestNotifications as $notification) {
            echo "    - {$notification->title}: {$notification->message}\n";
        }
    }
    echo "\n";
}

echo "Notification system check complete!\n";
