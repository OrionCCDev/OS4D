<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Task;
use App\Models\User;
use App\Models\UnifiedNotification;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG: Mark Email as Sent Issue ===\n\n";

// Test with task ID 44 (from your URL)
$taskId = 44;
echo "Testing with Task ID: $taskId\n";

try {
    $task = Task::find($taskId);
    if (!$task) {
        echo "❌ Task not found!\n";
        exit(1);
    }

    echo "✅ Task found: {$task->title}\n";
    echo "   Status: {$task->status}\n";
    echo "   Assigned to: {$task->assigned_to}\n";

    // Check email preparations
    $emailPreparations = $task->emailPreparations()
        ->whereIn('status', ['draft', 'processing'])
        ->orderBy('id', 'desc')
        ->get();

    echo "\n📧 Email Preparations:\n";
    if ($emailPreparations->isEmpty()) {
        echo "   ❌ No email preparations found with status 'draft' or 'processing'\n";

        // Check all email preparations
        $allEmailPreparations = $task->emailPreparations()->orderBy('id', 'desc')->get();
        echo "   All email preparations:\n";
        foreach ($allEmailPreparations as $ep) {
            echo "   - ID: {$ep->id}, Status: {$ep->status}, Created: {$ep->created_at}\n";
        }
    } else {
        foreach ($emailPreparations as $ep) {
            echo "   ✅ ID: {$ep->id}, Status: {$ep->status}, Subject: {$ep->subject}\n";
        }
    }

    // Test UnifiedNotification creation
    echo "\n🔔 Testing UnifiedNotification creation:\n";
    try {
        $testNotification = UnifiedNotification::createTaskNotification(
            1, // Use a test user ID
            'test_debug',
            'Test Debug Notification',
            'This is a test notification for debugging',
            ['test' => true],
            $taskId,
            'normal'
        );
        echo "   ✅ Notification created successfully: ID {$testNotification->id}\n";

        // Clean up test notification
        $testNotification->delete();
        echo "   🧹 Test notification cleaned up\n";
    } catch (Exception $e) {
        echo "   ❌ Notification creation failed: " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }

    // Test task history creation
    echo "\n📝 Testing task history creation:\n";
    try {
        $history = $task->histories()->create([
            'user_id' => 1, // Use a test user ID
            'action' => 'test_debug',
            'description' => 'Test debug history entry',
            'metadata' => ['test' => true]
        ]);
        echo "   ✅ History created successfully: ID {$history->id}\n";

        // Clean up test history
        $history->delete();
        echo "   🧹 Test history cleaned up\n";
    } catch (Exception $e) {
        echo "   ❌ History creation failed: " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }

    // Check database tables
    echo "\n🗄️ Database table checks:\n";

    // Check unified_notifications table
    try {
        $count = DB::table('unified_notifications')->count();
        echo "   ✅ unified_notifications table accessible: $count records\n";
    } catch (Exception $e) {
        echo "   ❌ unified_notifications table error: " . $e->getMessage() . "\n";
    }

    // Check task_histories table
    try {
        $count = DB::table('task_histories')->count();
        echo "   ✅ task_histories table accessible: $count records\n";
    } catch (Exception $e) {
        echo "   ❌ task_histories table error: " . $e->getMessage() . "\n";
    }

    // Check email_preparations table
    try {
        $count = DB::table('email_preparations')->where('task_id', $taskId)->count();
        echo "   ✅ email_preparations table accessible: $count records for task $taskId\n";
    } catch (Exception $e) {
        echo "   ❌ email_preparations table error: " . $e->getMessage() . "\n";
    }

    echo "\n=== Debug Complete ===\n";

} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
