<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== NOTIFICATION DISPLAY DEBUG ===\n\n";

// Check notifications for each manager
$managers = \App\Models\User::whereIn('role', ['admin', 'manager', 'sub-admin'])->get();

foreach ($managers as $manager) {
    echo "Manager: {$manager->name} ({$manager->email})\n";
    echo str_repeat("-", 60) . "\n";

    // Get unread notifications
    $unreadNotifications = \App\Models\UnifiedNotification::where('user_id', $manager->id)
        ->where('is_read', false)
        ->orderBy('created_at', 'desc')
        ->get();

    echo "Unread Notifications: " . $unreadNotifications->count() . "\n";

    if ($unreadNotifications->count() > 0) {
        echo "\nRecent unread notifications:\n";
        foreach ($unreadNotifications->take(10) as $notif) {
            echo "  • [{$notif->type}] {$notif->title}\n";
            echo "    Created: {$notif->created_at}\n";
            echo "    Message: " . substr($notif->message, 0, 80) . "...\n";
            echo "\n";
        }
    }

    // Get all notifications (last 7 days)
    $allNotifications = \App\Models\UnifiedNotification::where('user_id', $manager->id)
        ->where('created_at', '>=', now()->subDays(7))
        ->orderBy('created_at', 'desc')
        ->get();

    echo "Total Notifications (last 7 days): " . $allNotifications->count() . "\n";
    echo "  - Unread: " . $allNotifications->where('is_read', false)->count() . "\n";
    echo "  - Read: " . $allNotifications->where('is_read', true)->count() . "\n";

    // Break down by category
    echo "\nNotifications by category:\n";
    $byCategory = $allNotifications->groupBy('category');
    foreach ($byCategory as $category => $notifs) {
        echo "  - {$category}: " . $notifs->count() . "\n";
    }

    // Break down by type
    echo "\nNotifications by type:\n";
    $byType = $allNotifications->groupBy('type');
    foreach ($byType as $type => $notifs) {
        echo "  - {$type}: " . $notifs->count() . "\n";
    }

    echo "\n" . str_repeat("=", 60) . "\n\n";
}

// Check for users
echo "\n=== USER NOTIFICATIONS ===\n\n";
$users = \App\Models\User::where('role', 'user')->get();

foreach ($users as $user) {
    echo "User: {$user->name} ({$user->email})\n";
    echo str_repeat("-", 60) . "\n";

    // Get unread notifications
    $unreadNotifications = \App\Models\UnifiedNotification::where('user_id', $user->id)
        ->where('is_read', false)
        ->orderBy('created_at', 'desc')
        ->get();

    echo "Unread Notifications: " . $unreadNotifications->count() . "\n";

    if ($unreadNotifications->count() > 0) {
        echo "\nRecent unread notifications:\n";
        foreach ($unreadNotifications->take(5) as $notif) {
            echo "  • [{$notif->type}] {$notif->title}\n";
            echo "    Created: {$notif->created_at}\n";
            echo "\n";
        }
    }

    // Get all notifications (last 7 days)
    $allNotifications = \App\Models\UnifiedNotification::where('user_id', $user->id)
        ->where('created_at', '>=', now()->subDays(7))
        ->orderBy('created_at', 'desc')
        ->get();

    echo "Total Notifications (last 7 days): " . $allNotifications->count() . "\n";
    echo "  - Unread: " . $allNotifications->where('is_read', false)->count() . "\n";
    echo "  - Read: " . $allNotifications->where('is_read', true)->count() . "\n";

    echo "\n" . str_repeat("=", 60) . "\n\n";
}

echo "\n=== DEBUG COMPLETE ===\n";

