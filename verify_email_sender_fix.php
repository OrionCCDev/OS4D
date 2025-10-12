<?php

/**
 * EMAIL SENDER VERIFICATION SCRIPT
 *
 * This script verifies that each user sends emails from their own email address
 * and NOT from other users' email addresses (token mixing bug fix verification)
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\GmailOAuthService;
use Illuminate\Support\Facades\Log;

echo "=== EMAIL SENDER VERIFICATION TEST ===\n\n";

// Step 1: Check all users with Gmail connected
echo "STEP 1: Checking users with Gmail connected...\n";
$users = User::where('gmail_connected', true)->get();

if ($users->count() == 0) {
    echo "❌ No users with Gmail connected found\n";
    exit(1);
}

echo "Found " . $users->count() . " user(s) with Gmail connected:\n\n";

foreach ($users as $user) {
    echo "User ID: {$user->id}\n";
    echo "  Name: {$user->name}\n";
    echo "  Email: {$user->email}\n";
    echo "  Role: {$user->role}\n";
    echo "  Gmail Connected: " . ($user->gmail_connected ? 'Yes' : 'No') . "\n";
    echo "  Has Token: " . (!empty($user->gmail_token) ? 'Yes' : 'No') . "\n";
    echo "\n";
}

// Step 2: Test Gmail service isolation
echo "STEP 2: Testing Gmail service isolation...\n";
$gmailService = app(GmailOAuthService::class);

$testResults = [];

foreach ($users as $user) {
    echo "Testing user: {$user->name} ({$user->email})\n";

    // Get Gmail service for this user
    $service = $gmailService->getGmailService($user);

    if ($service) {
        echo "  ✓ Gmail service created successfully\n";

        // Get the actual Gmail email for verification
        $gmailEmail = $gmailService->getGmailEmail($user);
        if ($gmailEmail) {
            echo "  ✓ Gmail email retrieved: {$gmailEmail}\n";

            if ($gmailEmail === $user->email) {
                echo "  ✓✓ Email matches user's stored email - ISOLATION WORKING!\n";
                $testResults[$user->id] = [
                    'user' => $user,
                    'expected' => $user->email,
                    'actual' => $gmailEmail,
                    'success' => true
                ];
            } else {
                echo "  ❌ PROBLEM: Gmail email ({$gmailEmail}) doesn't match stored email ({$user->email})\n";
                $testResults[$user->id] = [
                    'user' => $user,
                    'expected' => $user->email,
                    'actual' => $gmailEmail,
                    'success' => false
                ];
            }
        } else {
            echo "  ⚠ Could not retrieve Gmail email\n";
            $testResults[$user->id] = [
                'user' => $user,
                'expected' => $user->email,
                'actual' => null,
                'success' => false
            ];
        }
    } else {
        echo "  ❌ Failed to create Gmail service\n";
        $testResults[$user->id] = [
            'user' => $user,
            'expected' => $user->email,
            'actual' => null,
            'success' => false
        ];
    }

    echo "\n";
}

// Step 3: Summary of results
echo "STEP 3: VERIFICATION SUMMARY\n";
echo "============================\n\n";

$successCount = 0;
$totalCount = count($testResults);

foreach ($testResults as $userId => $result) {
    $user = $result['user'];
    $status = $result['success'] ? '✅ PASS' : '❌ FAIL';

    echo "User {$user->id}: {$user->name} ({$user->email})\n";
    echo "  Status: {$status}\n";
    echo "  Expected: {$result['expected']}\n";
    echo "  Actual: " . ($result['actual'] ?? 'NULL') . "\n";

    if ($result['success']) {
        $successCount++;
    }
    echo "\n";
}

echo "OVERALL RESULT: {$successCount}/{$totalCount} users have correct email isolation\n\n";

if ($successCount === $totalCount && $totalCount > 0) {
    echo "🎉 SUCCESS! All users have isolated email addresses!\n";
    echo "🎉 The token mixing bug has been FIXED!\n";
    echo "\n";
    echo "NEXT STEP: Test actual email sending with real emails.\n";
    echo "Each user should now send emails from their own address.\n";
} else {
    echo "⚠️  WARNING: Some users may still have token mixing issues.\n";
    echo "Check the failed users above.\n";
}

echo "\n";
echo "=== REAL EMAIL SENDING TEST INSTRUCTIONS ===\n";
echo "1. Login as different users in different browsers\n";
echo "2. Go to a task with 'Email Preparation' section\n";
echo "3. Fill in email details and send\n";
echo "4. Check recipient inboxes to verify 'From' address\n";
echo "5. Each email should come from the sender's own email\n";

// Step 4: Check for any recent email sending logs
echo "\nSTEP 4: Checking recent email sending activity...\n";

$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $recentLogs = shell_exec("tail -50 " . escapeshellarg($logFile) . " | grep 'Gmail email sent successfully'");

    if ($recentLogs) {
        echo "Recent email sending activity found:\n";
        echo $recentLogs;
    } else {
        echo "No recent email sending activity found in logs.\n";
        echo "This is normal if no emails have been sent recently.\n";
    }
} else {
    echo "No log file found at: {$logFile}\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
