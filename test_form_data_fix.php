<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Task;
use App\Models\User;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST: Form Data Fix ===\n\n";

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
            echo "   To Emails: {$ep->to_emails}\n";
            echo "   CC Emails: {$ep->cc_emails}\n";
            echo "   Subject: {$ep->subject}\n";
        }
    }

    $emailPreparation = $emailPreparations->first();

    // Test the fixed markEmailAsSent functionality with proper form data
    echo "\n🧪 Testing markEmailAsSent functionality with form data fix...\n";

    // Simulate the request with proper form data
    $request = new \Illuminate\Http\Request();
    $request->merge([
        'sent_via' => 'gmail_manual',
        'to_emails' => $emailPreparation->to_emails,
        'cc_emails' => $emailPreparation->cc_emails,
        'bcc_emails' => $emailPreparation->bcc_emails,
        'subject' => $emailPreparation->subject,
        'body' => $emailPreparation->body,
        'save_draft' => '1'
    ]);

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
            echo "   ✅ markEmailAsSent SUCCESS! (Form Data Fix)\n";
            echo "   Message: " . $responseData['message'] . "\n";
            echo "   Redirect URL: " . $responseData['redirect_url'] . "\n";

            // Check if task status was updated
            $task->refresh();
            echo "   ✅ Task status updated to: {$task->status}\n";

            // Check if email preparation was updated
            $emailPreparation->refresh();
            echo "   ✅ Email preparation status updated to: {$emailPreparation->status}\n";

        } else {
            echo "   ❌ markEmailAsSent FAILED! (Form Data Fix)\n";
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
    echo "\n📋 What to test in browser:\n";
    echo "1. Go to: https://odc.com.orion-contracting.com/tasks/53/prepare-email\n";
    echo "2. Make sure the form fields are filled:\n";
    echo "   - To Emails field should have email addresses\n";
    echo "   - Subject field should have a subject\n";
    echo "   - Body field should have content\n";
    echo "3. Click 'Send From Outside App (Gmail)' button\n";
    echo "4. Click 'Done (Mark as Sent)' button in the modal\n";
    echo "5. Should work without 422 error now!\n";

} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
