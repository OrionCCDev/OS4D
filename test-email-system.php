<?php

require_once 'vendor/autoload.php';

use App\Services\ReliableEmailService;
use App\Models\User;
use App\Models\Email;

echo "🧪 Testing Reliable Email System...\n\n";

try {
    // Initialize Laravel
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    // Test IMAP connection
    echo "1. Testing IMAP connection...\n";
    $emailService = new ReliableEmailService();
    $result = $emailService->fetchNewEmails(5);

    if ($result['success']) {
        echo "✅ IMAP connection successful\n";
        echo "📧 Fetched {$result['total_fetched']} emails\n\n";
    } else {
        echo "❌ IMAP connection failed: " . implode(', ', $result['errors']) . "\n\n";
    }

    // Test email storage
    echo "2. Testing email storage...\n";
    $manager = User::whereIn('role', ['admin', 'manager'])->first();
    if ($manager) {
        $storeResult = $emailService->storeEmailsInDatabase($result['emails'], $manager);
        echo "✅ Stored: {$storeResult['stored']} emails\n";
        echo "⏭️  Skipped: {$storeResult['skipped']} duplicates\n\n";
    } else {
        echo "❌ No manager user found\n\n";
    }

    // Check recent emails
    echo "3. Checking recent emails in database...\n";
    $recentEmails = Email::where('created_at', '>=', now()->subMinutes(10))
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get(['id', 'from_email', 'subject', 'created_at']);

    foreach ($recentEmails as $email) {
        echo "📧 ID: {$email->id} | From: {$email->from_email} | Subject: {$email->subject} | Created: {$email->created_at}\n";
    }

    echo "\n✅ Test completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
