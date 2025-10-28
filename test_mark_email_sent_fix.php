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

echo "=== TEST: Mark Email as Sent Fix ===\n\n";

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

        // If no draft exists, create one for testing
        echo "\n🔧 Creating test email preparation...\n";
        $testEmailPrep = $task->emailPreparations()->create([
            'to_emails' => 'test@example.com',
            'cc_emails' => '',
            'bcc_emails' => '',
            'subject' => 'Test Email Subject',
            'body' => 'Test email body content',
            'status' => 'draft',
            'created_by' => 1, // Use a test user ID
        ]);
        echo "   ✅ Test email preparation created: ID {$testEmailPrep->id}\n";
        $emailPreparations = collect([$testEmailPrep]);
    } else {
        foreach ($emailPreparations as $ep) {
            echo "   ✅ ID: {$ep->id}, Status: {$ep->status}, Subject: {$ep->subject}\n";
        }
    }

    $emailPreparation = $emailPreparations->first();

    // Test the fixed markEmailAsSent functionality
    echo "\n🧪 Testing markEmailAsSent functionality...\n";

    // Simulate the request
    $request = new \Illuminate\Http\Request();
    $request->merge(['sent_via' => 'gmail_manual']);

    // Create a test user
    $testUser = User::first();
    if (!$testUser) {
        echo "   ❌ No users found in database\n";
        exit(1);
    }

    // Set the task as assigned to the test user for testing
    $task->update(['assigned_to' => $testUser->id]);
    echo "   ✅ Task assigned to test user: {$testUser->name}\n";

    // Authenticate as the test user
    Auth::login($testUser);
    echo "   ✅ Authenticated as: {$testUser->name}\n";

    // Test the controller method
    $controller = new \App\Http\Controllers\TaskController();

    try {
        $response = $controller->markEmailAsSent($request, $task);
        $responseData = json_decode($response->getContent(), true);

        if ($responseData['success']) {
            echo "   ✅ markEmailAsSent SUCCESS!\n";
            echo "   Message: " . $responseData['message'] . "\n";
            echo "   Redirect URL: " . $responseData['redirect_url'] . "\n";

            // Check if task status was updated
            $task->refresh();
            echo "   ✅ Task status updated to: {$task->status}\n";

            // Check if email preparation was updated
            $emailPreparation->refresh();
            echo "   ✅ Email preparation status updated to: {$emailPreparation->status}\n";

        } else {
            echo "   ❌ markEmailAsSent FAILED!\n";
            echo "   Error: " . $responseData['message'] . "\n";
            if (isset($responseData['debug'])) {
                echo "   Debug info: " . json_encode($responseData['debug'], JSON_PRETTY_PRINT) . "\n";
            }
        }

    } catch (Exception $e) {
        echo "   ❌ Exception during markEmailAsSent: " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }

    echo "\n=== Test Complete ===\n";

} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
