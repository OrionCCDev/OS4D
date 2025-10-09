<?php

/**
 * Verify if Gmail Email Was Actually Sent
 *
 * Checks Gmail sent folder to verify if confirmation email was actually sent
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TaskEmailPreparation;
use App\Models\User;
use App\Services\GmailOAuthService;
use Illuminate\Support\Facades\Log;

echo "\n";
echo "========================================\n";
echo "  VERIFY GMAIL EMAIL SENT              \n";
echo "========================================\n\n";

// Get the latest email preparation
$prep = TaskEmailPreparation::latest()->first();

if (!$prep) {
    echo "❌ No email preparations found.\n";
    exit;
}

echo "Checking Email Preparation:\n";
echo "---------------------------\n";
echo "ID: {$prep->id}\n";
echo "Task ID: {$prep->task_id}\n";
echo "Status: {$prep->status}\n";
echo "To: {$prep->to_emails}\n";
echo "Subject: {$prep->subject}\n";
echo "Sent At: " . ($prep->sent_at ?? 'NULL') . "\n\n";

// Get the user who sent it
$user = User::find($prep->prepared_by);
if (!$user) {
    echo "❌ User not found (ID: {$prep->prepared_by})\n";
    exit;
}

echo "Sender Information:\n";
echo "-------------------\n";
echo "Name: {$user->name}\n";
echo "Email: {$user->email}\n";
echo "Gmail Connected: " . ($user->gmail_connected ? 'YES' : 'NO') . "\n\n";

if ($user->gmail_connected) {
    echo "Checking Gmail Sent Folder...\n";
    echo "------------------------------\n";

    try {
        $gmailService = app(GmailOAuthService::class);
        $service = $gmailService->getGmailService($user);

        if (!$service) {
            echo "❌ Could not connect to Gmail service.\n";
            exit;
        }

        // Search for the email by subject in sent folder
        $subjectQuery = 'subject:"' . addslashes($prep->subject) . '" in:sent';
        $results = $service->users_messages->listUsersMessages('me', [
            'q' => $subjectQuery,
            'maxResults' => 5
        ]);

        if ($results->getMessages()) {
            echo "✅ Found " . count($results->getMessages()) . " sent email(s) matching this subject:\n\n";

            foreach ($results->getMessages() as $msg) {
                $message = $service->users_messages->get('me', $msg->getId());
                $headers = $message->getPayload()->getHeaders();

                $to = '';
                $date = '';
                foreach ($headers as $header) {
                    if ($header->getName() === 'To') {
                        $to = $header->getValue();
                    }
                    if ($header->getName() === 'Date') {
                        $date = $header->getValue();
                    }
                }

                echo "  Message ID: {$msg->getId()}\n";
                echo "  To: {$to}\n";
                echo "  Date: {$date}\n";

                // Check for attachments
                $parts = $message->getPayload()->getParts();
                if ($parts) {
                    $attachmentCount = 0;
                    foreach ($parts as $part) {
                        if ($part->getFilename() && $part->getBody()->getAttachmentId()) {
                            $attachmentCount++;
                        }
                    }
                    if ($attachmentCount > 0) {
                        echo "  Attachments: {$attachmentCount}\n";
                    }
                }
                echo "\n";
            }

            echo "✅ CONCLUSION: Email WAS successfully sent via Gmail!\n";
            echo "   Check the recipient's inbox (and spam folder).\n";

        } else {
            echo "⚠️ No sent emails found with this subject.\n\n";
            echo "❌ CONCLUSION: Email was NOT actually sent!\n";
            echo "   The job may have failed after marking it as 'sent'.\n";
        }

    } catch (\Exception $e) {
        echo "❌ Error checking Gmail: " . $e->getMessage() . "\n";
    }
} else {
    echo "⚠️ User doesn't have Gmail connected.\n";
    echo "   Email should have been sent via SMTP.\n\n";
    echo "Cannot verify SMTP emails - check mail server logs.\n";
}

echo "\n";
echo "========================================\n";
echo "  RECOMMENDATIONS                      \n";
echo "========================================\n\n";

if ($prep->status === 'sent' && $prep->sent_at) {
    echo "1. The email preparation is marked as 'sent'\n";
    echo "2. If not found in Gmail sent folder, the job failed after updating status\n";
    echo "3. Check Laravel logs for errors during sending\n";
    echo "4. Try sending a new test confirmation email\n";
} else {
    echo "1. The email was never marked as sent\n";
    echo "2. Check if queue worker is running\n";
    echo "3. Check for failed jobs\n";
}

echo "\n";

