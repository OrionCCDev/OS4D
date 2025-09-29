<?php
// Test script to check database and webhook
// Save this as test_email_tracking.php in your project root

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Email;
use App\Models\EmailNotification;
use App\Services\SimpleEmailTrackingService;

echo "=== Email Tracking Test ===\n\n";

// Check if there are any sent emails in database
$sentEmails = Email::where('email_type', 'sent')->get();
echo "Sent emails in database: " . $sentEmails->count() . "\n";

if ($sentEmails->count() > 0) {
    echo "Latest sent email:\n";
    $latest = $sentEmails->first();
    echo "- ID: " . $latest->id . "\n";
    echo "- Subject: " . $latest->subject . "\n";
    echo "- From: " . $latest->from_email . "\n";
    echo "- To: " . $latest->to_email . "\n";
    echo "- Sent at: " . $latest->sent_at . "\n";
    echo "- Replied at: " . ($latest->replied_at ?? 'Not replied') . "\n";
}

// Check notifications
$notifications = EmailNotification::all();
echo "\nEmail notifications: " . $notifications->count() . "\n";

// Test webhook processing
echo "\n=== Testing Webhook Processing ===\n";

$emailTrackingService = app(SimpleEmailTrackingService::class);

// Create test reply data
$testReplyData = [
    'from' => 'test@example.com',
    'to' => 'designers@orion-contracting.com',
    'subject' => 'Re: Test Email Subject',
    'body' => 'This is a test reply',
    'message_id' => 'test-' . time()
];

echo "Testing with reply data:\n";
echo "- From: " . $testReplyData['from'] . "\n";
echo "- Subject: " . $testReplyData['subject'] . "\n";

$result = $emailTrackingService->handleIncomingReply($testReplyData);
echo "Webhook processing result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";

echo "\n=== Test Complete ===\n";
