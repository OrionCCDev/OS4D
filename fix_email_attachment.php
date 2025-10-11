<?php

/**
 * Fix Email Attachment Issue
 * This script fixes the missing attachment data in the database
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Email;
use Illuminate\Support\Facades\Storage;

echo "=== FIXING EMAIL ATTACHMENT ISSUE ===\n\n";

try {
    // Get the specific email (ID 29)
    $email = Email::find(29);

    if (!$email) {
        echo "❌ Email with ID 29 not found!\n";
        exit;
    }

    echo "📧 Fixing Email ID: " . $email->id . "\n";
    echo "📧 Subject: " . $email->subject . "\n";
    echo "📧 Source: " . ($email->email_source ?? 'unknown') . "\n\n";

    // Check if file exists
    $filename = 'UAuNEVL8Ydi0IFpFF990XbCvEttg2DCUnBZad2oX.pdf';
    $filePath = storage_path('app/email-attachments/' . $filename);

    if (!file_exists($filePath)) {
        echo "❌ File not found: " . $filePath . "\n";
        exit;
    }

    $fileSize = filesize($filePath);
    $mimeType = mime_content_type($filePath) ?: 'application/pdf';

    echo "✅ File found: " . $filename . " ($fileSize bytes, $mimeType)\n";

    // Create attachment data
    $attachmentData = [
        'filename' => $filename,
        'mime_type' => $mimeType,
        'size' => $fileSize,
        'file_path' => 'email-attachments/' . $filename,
        'attachment_id' => null, // Not a Gmail API attachment
    ];

    // Update email with attachment data
    $email->attachments = [$attachmentData];
    $email->save();

    echo "✅ Updated email with attachment data\n";
    echo "📎 Attachment: " . $filename . "\n";
    echo "📎 Size: " . number_format($fileSize) . " bytes\n";
    echo "📎 MIME: " . $mimeType . "\n";
    echo "📎 Path: " . $attachmentData['file_path'] . "\n\n";

    // Verify the fix
    $updatedEmail = Email::find(29);
    $attachments = $updatedEmail->attachments ?? [];

    echo "🔍 Verification:\n";
    echo "Attachments count: " . count($attachments) . "\n";

    if (count($attachments) > 0) {
        $attachment = $attachments[0];
        echo "Filename: " . $attachment['filename'] . "\n";
        echo "Size: " . $attachment['size'] . " bytes\n";
        echo "MIME: " . $attachment['mime_type'] . "\n";
        echo "Path: " . $attachment['file_path'] . "\n";
        echo "\n✅ FIXED! The attachment should now be downloadable.\n";
    } else {
        echo "❌ Fix failed - no attachments found\n";
    }

    echo "\n=== END FIX ===\n";

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
