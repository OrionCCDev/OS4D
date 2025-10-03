<?php
/**
 * Notification Cleanup Script
 *
 * This script helps clean up duplicate notification data and consolidate
 * notifications into the unified notification system.
 *
 * Run this script from the project root:
 * php cleanup_notifications.php
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\UnifiedNotification;
use App\Models\DesignersInboxNotification;
use App\Models\CustomNotification;

echo "🔧 Starting notification cleanup process...\n\n";

try {
    // Get counts before cleanup
    $unifiedCount = UnifiedNotification::count();
    $designersCount = DesignersInboxNotification::count();
    $customCount = CustomNotification::count();

    echo "📊 Current notification counts:\n";
    echo "   - Unified Notifications: {$unifiedCount}\n";
    echo "   - Designers Inbox Notifications: {$designersCount}\n";
    echo "   - Custom Notifications: {$customCount}\n";
    echo "   - Total: " . ($unifiedCount + $designersCount + $customCount) . "\n\n";

    // Migrate Designers Inbox Notifications to Unified Notifications
    echo "🔄 Migrating Designers Inbox Notifications to Unified Notifications...\n";

    $designersNotifications = DesignersInboxNotification::whereNull('read_at')->get();
    $migratedCount = 0;

    foreach ($designersNotifications as $notification) {
        // Check if already exists in unified notifications
        $exists = UnifiedNotification::where('user_id', $notification->user_id)
            ->where('category', 'email')
            ->where('type', $notification->type)
            ->where('title', $notification->title)
            ->where('created_at', $notification->created_at)
            ->exists();

        if (!$exists) {
            UnifiedNotification::create([
                'user_id' => $notification->user_id,
                'category' => 'email',
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'data' => $notification->data,
                'email_id' => $notification->email_id,
                'is_read' => $notification->isRead(),
                'read_at' => $notification->read_at,
                'priority' => 'normal',
                'status' => 'active',
                'created_at' => $notification->created_at,
                'updated_at' => $notification->updated_at,
            ]);
            $migratedCount++;
        }
    }

    echo "   ✅ Migrated {$migratedCount} designers inbox notifications\n\n";

    // Migrate Custom Notifications to Unified Notifications
    echo "🔄 Migrating Custom Notifications to Unified Notifications...\n";

    $customNotifications = CustomNotification::where('read', false)->get();
    $migratedCustomCount = 0;

    foreach ($customNotifications as $notification) {
        // Determine category based on type
        $category = str_starts_with($notification->type, 'task_') ? 'task' : 'email';

        // Check if already exists in unified notifications
        $exists = UnifiedNotification::where('user_id', $notification->user_id)
            ->where('category', $category)
            ->where('type', $notification->type)
            ->where('title', $notification->title)
            ->where('created_at', $notification->created_at)
            ->exists();

        if (!$exists) {
            UnifiedNotification::create([
                'user_id' => $notification->user_id,
                'category' => $category,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'data' => $notification->data,
                'is_read' => $notification->read,
                'read_at' => $notification->read_at,
                'priority' => 'normal',
                'status' => 'active',
                'created_at' => $notification->created_at,
                'updated_at' => $notification->updated_at,
            ]);
            $migratedCustomCount++;
        }
    }

    echo "   ✅ Migrated {$migratedCustomCount} custom notifications\n\n";

    // Clean up old notification tables (optional - uncomment if you want to delete old data)
    /*
    echo "🗑️ Cleaning up old notification tables...\n";

    $deletedDesigners = DesignersInboxNotification::truncate();
    $deletedCustom = CustomNotification::truncate();

    echo "   ✅ Deleted old designers inbox notifications\n";
    echo "   ✅ Deleted old custom notifications\n\n";
    */

    // Get final counts
    $finalUnifiedCount = UnifiedNotification::count();
    $finalDesignersCount = DesignersInboxNotification::count();
    $finalCustomCount = CustomNotification::count();

    echo "📊 Final notification counts:\n";
    echo "   - Unified Notifications: {$finalUnifiedCount}\n";
    echo "   - Designers Inbox Notifications: {$finalDesignersCount}\n";
    echo "   - Custom Notifications: {$finalCustomCount}\n";
    echo "   - Total: " . ($finalUnifiedCount + $finalDesignersCount + $finalCustomCount) . "\n\n";

    echo "✅ Notification cleanup completed successfully!\n";
    echo "🎯 All notification systems now use the unified notification system.\n";
    echo "🔔 Notification counts should now be consistent across the application.\n\n";

    echo "📝 Next steps:\n";
    echo "   1. Test the notification system in your browser\n";
    echo "   2. Check that notification counts are now consistent\n";
    echo "   3. If everything works correctly, you can uncomment the cleanup section\n";
    echo "      to delete the old notification tables\n";

} catch (Exception $e) {
    echo "❌ Error during cleanup: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
