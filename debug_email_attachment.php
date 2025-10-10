<?php

/**
 * Debug Email Attachment Issue
 * This script checks the specific email and its attachment data
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Email;
use Illuminate\Support\Facades\Storage;

echo "=== EMAIL ATTACHMENT DEBUG ===\n\n";

try {
    // Get the specific email (ID 29 from the URL)
    $email = Email::find(29);

    if (!$email) {
        echo "❌ Email with ID 29 not found!\n";
        exit;
    }

    echo "📧 Email ID: " . $email->id . "\n";
    echo "📧 Subject: " . $email->subject . "\n";
    echo "📧 From: " . $email->from_email . "\n";
    echo "📧 To: " . $email->to_email . "\n";
    echo "📧 Source: " . ($email->email_source ?? 'unknown') . "\n\n";

    // Check attachments
    $attachments = $email->attachments ?? [];
    echo "📎 Attachments Count: " . count($attachments) . "\n\n";

    if (empty($attachments)) {
        echo "❌ No attachments found in email data!\n";
        exit;
    }

    // Check each attachment
    foreach ($attachments as $index => $attachment) {
        echo "--- Attachment " . ($index + 1) . " ---\n";
        echo "Filename: " . ($attachment['filename'] ?? 'unknown') . "\n";
        echo "MIME Type: " . ($attachment['mime_type'] ?? 'unknown') . "\n";
        echo "Size: " . ($attachment['size'] ?? 'unknown') . " bytes\n";
        echo "Attachment ID: " . ($attachment['attachment_id'] ?? 'none') . "\n";
        echo "File Path: " . ($attachment['file_path'] ?? 'none') . "\n";

        // Check if it's a Gmail attachment
        if (isset($attachment['attachment_id']) && $email->email_source === 'gmail') {
            echo "🔍 Type: Gmail API attachment (needs Gmail API to download)\n";
        } else {
            echo "🔍 Type: Local file attachment\n";

            // Check possible file locations
            $filename = $attachment['filename'] ?? 'unknown';
            $filePath = $attachment['file_path'] ?? null;

            $possiblePaths = [
                storage_path('app/email-attachments/' . $filename),
                storage_path('app/' . $filename),
            ];

            if ($filePath) {
                $possiblePaths[] = storage_path('app/' . $filePath);
            }

            echo "🔍 Checking file locations:\n";
            foreach ($possiblePaths as $path) {
                $exists = file_exists($path);
                $size = $exists ? filesize($path) : 0;
                echo "  - " . $path . " - " . ($exists ? "✅ EXISTS ($size bytes)" : "❌ NOT FOUND") . "\n";
            }
        }

        echo "\n";
    }

    // Check storage directory
    echo "📁 Storage Directory Check:\n";
    $storageDir = storage_path('app/email-attachments');
    echo "Storage dir: " . $storageDir . "\n";
    echo "Directory exists: " . (is_dir($storageDir) ? "✅ YES" : "❌ NO") . "\n";

    if (is_dir($storageDir)) {
        $files = scandir($storageDir);
        $files = array_filter($files, function($file) {
            return $file !== '.' && $file !== '..';
        });
        echo "Files in directory: " . count($files) . "\n";
        foreach ($files as $file) {
            $fullPath = $storageDir . '/' . $file;
            $size = filesize($fullPath);
            echo "  - " . $file . " ($size bytes)\n";
        }
    }

    echo "\n=== END DEBUG ===\n";

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
