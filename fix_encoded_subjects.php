<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔧 Fixing encoded email subjects in database...\n";

try {
    // Get all emails with encoded subjects
    $emails = DB::table('emails')
        ->where('subject', 'like', '=?UTF-8?%')
        ->orWhere('subject', 'like', '=?utf-8?%')
        ->get();

    echo "Found " . $emails->count() . " emails with encoded subjects\n";

    $fixed = 0;
    foreach ($emails as $email) {
        $originalSubject = $email->subject;

        // Decode the subject
        $decodedSubject = mb_decode_mimeheader($originalSubject);

        // If decoding failed, try alternative method
        if ($decodedSubject === $originalSubject) {
            $decodedSubject = iconv_mime_decode($originalSubject, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
        }

        // Only update if we got a different result
        if ($decodedSubject && $decodedSubject !== $originalSubject) {
            DB::table('emails')
                ->where('id', $email->id)
                ->update(['subject' => $decodedSubject]);

            $fixed++;
            echo "Fixed email ID {$email->id}: {$originalSubject} -> {$decodedSubject}\n";
        }
    }

    echo "✅ Fixed {$fixed} email subjects\n";

    // Also fix notifications
    $notifications = DB::table('unified_notifications')
        ->where('message', 'like', '%=?UTF-8?%')
        ->orWhere('message', 'like', '%=?utf-8?%')
        ->get();

    echo "Found " . $notifications->count() . " notifications with encoded subjects\n";

    $fixedNotifications = 0;
    foreach ($notifications as $notification) {
        $originalMessage = $notification->message;

        // Decode the message
        $decodedMessage = mb_decode_mimeheader($originalMessage);

        // If decoding failed, try alternative method
        if ($decodedMessage === $originalMessage) {
            $decodedMessage = iconv_mime_decode($originalMessage, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
        }

        // Only update if we got a different result
        if ($decodedMessage && $decodedMessage !== $originalMessage) {
            DB::table('unified_notifications')
                ->where('id', $notification->id)
                ->update(['message' => $decodedMessage]);

            $fixedNotifications++;
        }
    }

    echo "✅ Fixed {$fixedNotifications} notification messages\n";
    echo "🎉 All encoded subjects have been fixed!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
