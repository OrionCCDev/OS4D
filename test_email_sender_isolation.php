<?php

/**
 * Email Sender Isolation Test
 *
 * This script tests that each user sends emails from their own email address
 * and not from other users' email addresses (token mixing bug fix)
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\GmailOAuthService;
use Illuminate\Support\Facades\Log;

echo "=== EMAIL SENDER ISOLATION TEST ===\n\n";

// Get all users with Gmail connected
$users = User::where('gmail_connected', true)->get();

if ($users->count() == 0) {
    echo "❌ No users with Gmail connected found\n";
    echo "Please connect Gmail for at least 2 users to test\n";
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

echo "=== TESTING EMAIL SERVICE ISOLATION ===\n\n";

$gmailService = app(GmailOAuthService::class);

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
                echo "  ✓ Email matches user's stored email\n";
            } else {
                echo "  ⚠ Warning: Gmail email ({$gmailEmail}) doesn't match stored email ({$user->email})\n";
            }
        } else {
            echo "  ⚠ Could not retrieve Gmail email\n";
        }
    } else {
        echo "  ❌ Failed to create Gmail service\n";
    }

    echo "\n";
}

echo "=== CHECKING FOR TOKEN ISOLATION ===\n\n";

if ($users->count() >= 2) {
    echo "Testing that users don't share tokens...\n\n";

    $user1 = $users[0];
    $user2 = $users[1];

    echo "User 1: {$user1->name} ({$user1->email})\n";
    echo "User 2: {$user2->name} ({$user2->email})\n\n";

    // Create services for both users
    $service1 = $gmailService->getGmailService($user1);
    $service2 = $gmailService->getGmailService($user2);

    if ($service1 && $service2) {
        echo "  ✓ Both services created successfully\n";

        // Get emails for both
        $email1 = $gmailService->getGmailEmail($user1);
        $email2 = $gmailService->getGmailEmail($user2);

        echo "  User 1 Gmail: {$email1}\n";
        echo "  User 2 Gmail: {$email2}\n\n";

        if ($email1 === $user1->email && $email2 === $user2->email) {
            echo "  ✓✓✓ SUCCESS! Each user has their own correct email address\n";
            echo "  ✓✓✓ Token mixing bug is FIXED!\n";
        } else {
            echo "  ❌ PROBLEM! Users may still be sharing tokens\n";
            echo "     User 1 expected: {$user1->email}, got: {$email1}\n";
            echo "     User 2 expected: {$user2->email}, got: {$email2}\n";
        }
    } else {
        echo "  ❌ Could not create services for testing\n";
    }
} else {
    echo "⚠ Need at least 2 users with Gmail connected to test token isolation\n";
}

echo "\n=== TEST COMPLETE ===\n";

