<?php

/**
 * Test Email Attachments Display
 *
 * This script checks if email attachments are properly stored and can be displayed
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Email;

echo "\n";
echo "========================================\n";
echo "  EMAIL ATTACHMENTS DEBUG TEST        \n";
echo "========================================\n\n";

// Get the email from the image (ID 28)
$emailId = 28;

try {
    $email = Email::findOrFail($emailId);

    echo "📧 Email Details:\n";
    echo "-----------------\n";
    echo "ID: {$email->id}\n";
    echo "Subject: {$email->subject}\n";
    echo "From: {$email->from_email}\n";
    echo "To: {$email->to_email}\n";
    echo "CC: {$email->cc}\n";
    echo "CC Emails (JSON): " . json_encode($email->cc_emails) . "\n";
    echo "Attachments (JSON): " . json_encode($email->attachments) . "\n";
    echo "Email Type: {$email->email_type}\n";
    echo "Email Source: {$email->email_source}\n\n";

    // Check attachments
    if (!empty($email->attachments)) {
        echo "📎 Attachments Found:\n";
        echo "--------------------\n";
        if (is_array($email->attachments)) {
            foreach ($email->attachments as $index => $attachment) {
                echo "Attachment {$index}:\n";
                echo "  Filename: " . ($attachment['filename'] ?? 'Unknown') . "\n";
                echo "  MIME Type: " . ($attachment['mime_type'] ?? 'Unknown') . "\n";
                echo "  Size: " . (isset($attachment['size']) ? number_format($attachment['size']) . ' bytes' : 'Unknown') . "\n";
                echo "  Attachment ID: " . ($attachment['attachment_id'] ?? 'None') . "\n";
                echo "  Part Number: " . ($attachment['part_number'] ?? 'None') . "\n\n";
            }
        } else {
            echo "Attachments field is not an array: " . gettype($email->attachments) . "\n";
            echo "Raw value: " . var_export($email->attachments, true) . "\n\n";
        }
    } else {
        echo "❌ No attachments found in database\n\n";
    }

    // Check if this is a sent email (from our system)
    if ($email->email_type === 'sent' || $email->email_source === 'task_confirmation') {
        echo "📤 This appears to be a SENT email from our system\n";
        echo "Sent emails store attachments differently than received emails\n";
        echo "Checking TaskEmailPreparation for attachments...\n\n";

        // Try to find the task email preparation
        $taskEmailPrep = \App\Models\TaskEmailPreparation::where('task_id', $email->task_id ?? 0)
            ->where('status', 'sent')
            ->first();

        if ($taskEmailPrep) {
            echo "Found TaskEmailPreparation:\n";
            echo "Attachments: " . json_encode($taskEmailPrep->attachments) . "\n\n";
        } else {
            echo "No TaskEmailPreparation found\n\n";
        }
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "========================================\n";
echo "  ATTACHMENTS DEBUG COMPLETE          \n";
echo "========================================\n\n";
