<?php
/**
 * Test Notification Counts Script
 *
 * This script tests the notification counts to verify they are consistent
 * across different notification systems.
 *
 * Run this script from the project root:
 * php test_notification_counts.php
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\UnifiedNotification;
use App\Models\DesignersInboxNotification;
use App\Models\CustomNotification;

echo "🧪 Testing notification counts...\n\n";

try {
    // Get counts from all notification systems
    $unifiedCount = UnifiedNotification::where('is_read', false)->where('status', 'active')->count();
    $designersCount = DesignersInboxNotification::whereNull('read_at')->count();
    $customCount = CustomNotification::where('read', false)->count();

    echo "📊 Notification counts by system:\n";
    echo "   - Unified Notifications (unread): {$unifiedCount}\n";
    echo "   - Designers Inbox Notifications (unread): {$designersCount}\n";
    echo "   - Custom Notifications (unread): {$customCount}\n";
    echo "   - Total unread: " . ($unifiedCount + $designersCount + $customCount) . "\n\n";

    // Get counts by category in unified system
    $taskUnread = UnifiedNotification::where('category', 'task')->where('is_read', false)->where('status', 'active')->count();
    $emailUnread = UnifiedNotification::where('category', 'email')->where('is_read', false)->where('status', 'active')->count();

    echo "📊 Unified notification counts by category:\n";
    echo "   - Task notifications (unread): {$taskUnread}\n";
    echo "   - Email notifications (unread): {$emailUnread}\n";
    echo "   - Total unified (unread): " . ($taskUnread + $emailUnread) . "\n\n";

    // Check for potential duplicates
    echo "🔍 Checking for potential duplicates...\n";

    $duplicates = DB::select("
        SELECT user_id, category, type, title, created_at, COUNT(*) as count
        FROM unified_notifications
        WHERE status = 'active'
        GROUP BY user_id, category, type, title, created_at
        HAVING COUNT(*) > 1
    ");

    if (count($duplicates) > 0) {
        echo "   ⚠️  Found " . count($duplicates) . " potential duplicate notifications\n";
        foreach ($duplicates as $dup) {
            echo "      - User {$dup->user_id}: {$dup->category}/{$dup->type} - {$dup->title} ({$dup->count} copies)\n";
        }
    } else {
        echo "   ✅ No duplicate notifications found\n";
    }

    echo "\n";

    // Test the notification service
    echo "🧪 Testing notification service...\n";

    $user = \App\Models\User::first();
    if ($user) {
        $notificationService = new \App\Services\NotificationService();
        $stats = $notificationService->getNotificationStats($user->id);

        echo "   - User ID: {$user->id}\n";
        echo "   - Total notifications: {$stats['total']}\n";
        echo "   - Unread notifications: {$stats['unread']}\n";
        echo "   - Task unread: {$stats['task_unread']}\n";
        echo "   - Email unread: {$stats['email_unread']}\n";
        echo "   - Read notifications: {$stats['read']}\n";
    } else {
        echo "   ⚠️  No users found in database\n";
    }

    echo "\n";

    // Summary
    if ($unifiedCount > 0 && $unifiedCount == ($taskUnread + $emailUnread)) {
        echo "✅ Notification counts are consistent!\n";
        echo "🎯 The unified notification system is working correctly.\n";
    } else {
        echo "⚠️  Notification counts may be inconsistent.\n";
        echo "🔧 Consider running the cleanup script to consolidate notifications.\n";
    }

} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
