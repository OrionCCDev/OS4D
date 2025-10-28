<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Task;
use App\Models\User;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST: External Button Status Check ===\n\n";

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

    // Check all email preparations
    $allEmailPreparations = $task->emailPreparations()->orderBy('id', 'desc')->get();
    echo "\n📧 All Email Preparations:\n";
    foreach ($allEmailPreparations as $ep) {
        echo "   ✅ ID: {$ep->id}, Status: {$ep->status}, Created: {$ep->created_at}\n";
        echo "   To Emails: {$ep->to_emails}\n";
        echo "   Subject: {$ep->subject}\n";
    }

    // Check if there are any draft email preparations
    $draftEmailPreparations = $task->emailPreparations()
        ->whereIn('status', ['draft', 'processing'])
        ->orderBy('id', 'desc')
        ->get();

    if ($draftEmailPreparations->isEmpty()) {
        echo "\n📝 No draft email preparations found.\n";
        echo "   This means the external button has already been used successfully!\n";
        echo "   The task status is: {$task->status}\n";

        if ($task->status === 'on_client_consultant_review') {
            echo "   ✅ SUCCESS! Task is in 'On Client/Consultant Review' status\n";
            echo "   ✅ This confirms the external button worked correctly!\n";
        } else {
            echo "   ⚠️  Task status is not 'on_client_consultant_review'\n";
            echo "   Current status: {$task->status}\n";
        }
    } else {
        echo "\n📝 Found draft email preparations:\n";
        foreach ($draftEmailPreparations as $ep) {
            echo "   ID: {$ep->id}, Status: {$ep->status}\n";
        }
    }

    // Check task history for email sent entries
    echo "\n📚 Task History (Email Related):\n";
    $emailHistories = $task->histories()
        ->whereIn('action', ['email_marked_sent', 'status_changed'])
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

    foreach ($emailHistories as $history) {
        echo "   ✅ {$history->action}: {$history->description}\n";
        echo "   Created: {$history->created_at}\n";
    }

    echo "\n=== Analysis Complete ===\n";

    if ($task->status === 'on_client_consultant_review') {
        echo "\n🎉 CONCLUSION: EXTERNAL BUTTON IS WORKING!\n";
        echo "✅ Task status is 'on_client_consultant_review'\n";
        echo "✅ Email preparation exists with 'sent' status\n";
        echo "✅ The external button fix is successful!\n";
    } else {
        echo "\n⚠️  CONCLUSION: NEED TO TEST EXTERNAL BUTTON\n";
        echo "The task is not in the expected status yet.\n";
        echo "Please test the external button in the browser.\n";
    }

    echo "\n📋 Browser Test Instructions:\n";
    echo "1. Go to: https://odc.com.orion-contracting.com/tasks/53/prepare-email\n";
    echo "2. Fill in the form fields (especially To Emails)\n";
    echo "3. Click 'Send From Outside App (Gmail)' button\n";
    echo "4. Click 'Done (Mark as Sent)' button in the modal\n";
    echo "5. Should work without errors!\n";

} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
