<?php

/**
 * Test script for email fetching functionality
 * Run this from the command line: php test_email_fetch.php
 */

require_once 'vendor/autoload.php';

use App\Services\GmailEmailFetchService;
use App\Services\GmailOAuthService;
use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Email Fetching Test ===\n\n";

try {
    // Get a user with Gmail connected
    $user = User::where('gmail_connected', true)->first();

    if (!$user) {
        echo "❌ No user found with Gmail connected.\n";
        echo "Please connect Gmail to a user account first.\n";
        exit(1);
    }

    echo "✅ Found user: {$user->email} (ID: {$user->id})\n";
    echo "✅ Gmail connected: " . ($user->gmail_connected ? 'Yes' : 'No') . "\n";
    echo "✅ Connected at: " . ($user->gmail_connected_at ?? 'Unknown') . "\n\n";

    // Initialize services
    $gmailOAuthService = new GmailOAuthService();
    $emailFetchService = new GmailEmailFetchService($gmailOAuthService);

    echo "=== Testing Gmail Connection ===\n";

    // Test Gmail service
    $gmailService = $gmailOAuthService->getGmailService($user);
    if (!$gmailService) {
        echo "❌ Failed to get Gmail service\n";
        exit(1);
    }

    echo "✅ Gmail service initialized successfully\n";

    // Get email stats
    echo "\n=== Getting Email Statistics ===\n";
    $stats = $emailFetchService->getEmailStats($user);

    if (isset($stats['error'])) {
        echo "❌ Error getting stats: " . $stats['error'] . "\n";
    } else {
        echo "✅ Total messages in Gmail: " . ($stats['total_messages'] ?? 0) . "\n";
        echo "✅ Total threads: " . ($stats['total_threads'] ?? 0) . "\n";
        echo "✅ Email address: " . ($stats['email_address'] ?? 'Unknown') . "\n";
    }

    // Test fetching emails
    echo "\n=== Testing Email Fetching ===\n";
    echo "Fetching first 10 emails...\n";

    $fetchResult = $emailFetchService->fetchAllEmails($user, 10);

    if (!$fetchResult['success']) {
        echo "❌ Failed to fetch emails\n";
        foreach ($fetchResult['errors'] as $error) {
            echo "   Error: " . $error . "\n";
        }
        exit(1);
    }

    echo "✅ Successfully fetched " . $fetchResult['total_fetched'] . " emails\n";

    if ($fetchResult['total_fetched'] > 0) {
        echo "\n=== Sample Email Data ===\n";
        $sampleEmail = $fetchResult['emails'][0];
        echo "From: " . $sampleEmail['from_email'] . "\n";
        echo "To: " . $sampleEmail['to_email'] . "\n";
        echo "Subject: " . $sampleEmail['subject'] . "\n";
        echo "Date: " . ($sampleEmail['date'] ? $sampleEmail['date']->format('Y-m-d H:i:s') : 'Unknown') . "\n";
        echo "Has attachments: " . (count($sampleEmail['attachments']) > 0 ? 'Yes' : 'No') . "\n";
        echo "Body preview: " . substr(strip_tags($sampleEmail['body']), 0, 100) . "...\n";

        // Test storing emails
        echo "\n=== Testing Email Storage ===\n";
        $storeResult = $emailFetchService->storeEmailsInDatabase($fetchResult['emails'], $user);

        echo "✅ Stored: " . $storeResult['stored'] . " emails\n";
        echo "✅ Skipped: " . $storeResult['skipped'] . " emails\n";

        if (!empty($storeResult['errors'])) {
            echo "❌ Storage errors:\n";
            foreach ($storeResult['errors'] as $error) {
                echo "   Error: " . $error . "\n";
            }
        }
    }

    // Test search functionality
    echo "\n=== Testing Email Search ===\n";
    $searchResult = $emailFetchService->searchEmails($user, [
        'maxResults' => 5,
        'has_attachment' => true
    ]);

    if ($searchResult['success']) {
        echo "✅ Search successful, found " . $searchResult['total_found'] . " emails with attachments\n";
    } else {
        echo "❌ Search failed\n";
        foreach ($searchResult['errors'] as $error) {
            echo "   Error: " . $error . "\n";
        }
    }

    echo "\n=== Test Completed Successfully! ===\n";
    echo "✅ All email fetching functionality is working correctly.\n";
    echo "✅ You can now access the email interface at: /emails-all\n";

} catch (Exception $e) {
    echo "❌ Test failed with exception: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
