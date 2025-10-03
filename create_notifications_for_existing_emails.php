<?php
/**
 * Create Notifications for Existing Emails Script
 *
 * This script creates notifications for existing emails that don't have notifications yet.
 * This is useful to backfill notifications after fixing the notification service.
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "=== Create Notifications for Existing Emails ===\n";
echo "This script will create notifications for existing emails that don't have notifications yet.\n\n";

try {
    // Initialize Laravel application
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    echo "✅ Laravel application initialized\n";

    // Check if there are users who should receive notifications
    $notificationUsers = \App\Models\User::whereIn('role', ['admin', 'manager'])->get();

    if ($notificationUsers->count() === 0) {
        echo "❌ No admin/manager users found. Cannot create notifications.\n";
        echo "Please ensure you have users with 'admin' or 'manager' roles.\n";
        exit(1);
    }

    echo "✅ Found {$notificationUsers->count()} users who will receive notifications:\n";
    foreach ($notificationUsers as $user) {
        echo "  - {$user->name} ({$user->email}) - Role: {$user->role}\n";
    }

    echo "\n";

    // Find emails that don't have notifications yet
    echo "=== Finding Emails Without Notifications ===\n";

    $emailsWithoutNotifications = \App\Models\Email::where('email_source', 'designers_inbox')
        ->whereDoesntHave('designersInboxNotifications')
        ->orderBy('received_at', 'desc')
        ->get();

    echo "Found {$emailsWithoutNotifications->count()} emails without notifications\n";

    if ($emailsWithoutNotifications->count() === 0) {
        echo "✅ All emails already have notifications!\n";
        exit(0);
    }

    // Show some sample emails
    echo "\nSample emails without notifications:\n";
    foreach ($emailsWithoutNotifications->take(5) as $email) {
        $receivedTime = $email->received_at->diffForHumans();
        echo "  - {$email->subject} from {$email->from_email} ({$receivedTime})\n";
    }

    echo "\n";

    // Ask for confirmation
    echo "=== Confirmation Required ===\n";
    echo "This will create notifications for {$emailsWithoutNotifications->count()} emails.\n";
    echo "Each email will create " . $notificationUsers->count() . " notifications (one per admin/manager user).\n";
    echo "Total notifications to be created: " . ($emailsWithoutNotifications->count() * $notificationUsers->count()) . "\n\n";

    echo "Do you want to proceed? (y/N): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);

    if (trim(strtolower($line)) !== 'y') {
        echo "Operation cancelled.\n";
        exit(0);
    }

    echo "\n=== Creating Notifications ===\n";

    $notificationService = new \App\Services\DesignersInboxNotificationService();
    $createdCount = 0;
    $errorCount = 0;

    foreach ($emailsWithoutNotifications as $email) {
        try {
            // Create notifications for this email
            $notificationService->processEmailNotifications($email);
            $createdCount++;

            if ($createdCount % 10 === 0) {
                echo "✅ Processed {$createdCount} emails...\n";
            }

        } catch (\Exception $e) {
            $errorCount++;
            echo "❌ Error creating notifications for email {$email->id}: " . $e->getMessage() . "\n";
        }
    }

    echo "\n=== Results ===\n";
    echo "✅ Successfully processed: {$createdCount} emails\n";
    echo "❌ Errors: {$errorCount} emails\n";

    // Check final notification count
    $finalNotificationCount = \App\Models\DesignersInboxNotification::count();
    echo "📊 Total notifications in database: {$finalNotificationCount}\n";

    // Check unread count
    $unreadCount = \App\Models\DesignersInboxNotification::unread()->count();
    echo "📊 Unread notifications: {$unreadCount}\n";

    echo "\n✅ Notification creation completed!\n";
    echo "\nNext steps:\n";
    echo "1. Visit https://odc.com.orion-contracting.com/\n";
    echo "2. Check the navbar envelope icon - it should now show notifications\n";
    echo "3. Click the envelope to see the notification dropdown\n";
    echo "4. Test the notification system with new emails\n\n";

} catch (Exception $e) {
    echo "❌ Script failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
