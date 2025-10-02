<?php
/**
 * Test script to check notification creation and count
 */

require_once 'vendor/autoload.php';

use App\Models\DesignersInboxNotification;
use App\Models\User;
use App\Services\AutoEmailFetchService;
use App\Services\DesignersInboxEmailService;
use App\Services\DesignersInboxNotificationService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Notification Test ===\n";

try {
    // Get all managers
    $managers = User::whereIn('role', ['admin', 'manager'])->get();
    echo "Found " . $managers->count() . " managers:\n";
    foreach ($managers as $manager) {
        echo "  - {$manager->name} (ID: {$manager->id})\n";
    }

    echo "\n1. Checking total notifications in database...\n";
    $totalNotifications = DesignersInboxNotification::count();
    echo "   Total notifications: {$totalNotifications}\n";

    echo "\n2. Checking unread notifications per manager...\n";
    foreach ($managers as $manager) {
        $unreadCount = DesignersInboxNotification::where('user_id', $manager->id)
            ->whereNull('read_at')
            ->count();
        echo "   {$manager->name}: {$unreadCount} unread notifications\n";
    }

    echo "\n3. Recent notifications (last 5):\n";
    $recentNotifications = DesignersInboxNotification::with('email')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

    foreach ($recentNotifications as $notification) {
        $emailSubject = $notification->email ? $notification->email->subject : 'No email';
        $isRead = $notification->read_at ? 'READ' : 'UNREAD';
        echo "   - [{$isRead}] {$notification->title} - {$emailSubject}\n";
    }

    echo "\n4. Testing notification count API...\n";
    $notificationService = new DesignersInboxNotificationService();
    $autoEmailService = new AutoEmailFetchService(
        new DesignersInboxEmailService($notificationService),
        $notificationService
    );

    $unreadCount = $autoEmailService->getUnreadNotificationsCount();
    echo "   API unread count: {$unreadCount}\n";

    echo "\n=== Test completed! ===\n";

} catch (Exception $e) {
    echo "❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
