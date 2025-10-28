<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Task;
use App\Models\User;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PRODUCTION TEST: Modal Data Fix ===\n\n";

// Test with task ID 53 (from your URL)
$taskId = 53;
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

        echo "\n🔧 To test the external button, you need to:\n";
        echo "1. Reset an existing email preparation to 'draft' status, OR\n";
        echo "2. Create a new task for testing\n";

        // Show how to reset if needed
        if ($allEmailPreparations->isNotEmpty()) {
            $latestEp = $allEmailPreparations->first();
            echo "\n💡 To reset the latest email preparation for testing:\n";
            echo "   UPDATE task_email_preparations SET status = 'draft' WHERE id = {$latestEp->id};\n";
        }

    } else {
        foreach ($emailPreparations as $ep) {
            echo "   ✅ ID: {$ep->id}, Status: {$ep->status}, Subject: {$ep->subject}\n";
            echo "   To Emails: {$ep->to_emails}\n";
            echo "   CC Emails: {$ep->cc_emails}\n";
        }
    }

    echo "\n=== Test Complete ===\n";
    echo "\n📋 What to test in browser:\n";
    echo "1. Go to: https://odc.com.orion-contracting.com/tasks/53/prepare-email\n";
    echo "2. Click 'Send From Outside App (Gmail)' button\n";
    echo "3. Verify the modal shows populated data:\n";
    echo "   - Project Manager Email (TO field)\n";
    echo "   - CC Emails\n";
    echo "   - Suggested Subject\n";
    echo "   - Email Body Preview\n";
    echo "4. Click 'Done (Mark as Sent)' button in the modal\n";
    echo "5. Should work without 'to emails field is required' error!\n";
    echo "\n🔍 The fix now uses modal field data instead of main form data.\n";

} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
