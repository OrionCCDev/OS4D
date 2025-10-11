<?php

/**
 * Check Email Record Details
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Email;

echo "=== EMAIL RECORD DETAILS ===\n\n";

try {
    $email = Email::find(29);

    if (!$email) {
        echo "❌ Email not found!\n";
        exit;
    }

    echo "📧 Email ID: " . $email->id . "\n";
    echo "📧 Subject: " . $email->subject . "\n";
    echo "📧 From: " . $email->from_email . "\n";
    echo "📧 To: " . $email->to_email . "\n";
    echo "📧 Source: " . ($email->email_source ?? 'null') . "\n";
    echo "📧 Type: " . ($email->email_type ?? 'null') . "\n";
    echo "📧 Gmail Message ID: " . ($email->gmail_message_id ?? 'null') . "\n";
    echo "📧 Thread ID: " . ($email->thread_id ?? 'null') . "\n";
    echo "📧 User ID: " . ($email->user_id ?? 'null') . "\n";
    echo "📧 Created: " . $email->created_at . "\n";
    echo "📧 Updated: " . $email->updated_at . "\n";

    echo "\n📎 Attachments (raw): " . ($email->attachments ?? 'null') . "\n";

    $attachments = $email->attachments ?? [];
    echo "📎 Attachments (decoded): " . json_encode($attachments, JSON_PRETTY_PRINT) . "\n";

    echo "\n=== END ===\n";

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
