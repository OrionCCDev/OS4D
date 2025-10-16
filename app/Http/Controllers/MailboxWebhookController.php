<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReliableEmailService;
use App\Services\NotificationService;
use App\Models\User;
use App\Models\Email;
use Illuminate\Support\Facades\Log;

class MailboxWebhookController extends Controller
{
    protected $emailService;
    protected $notificationService;

    public function __construct(
        ReliableEmailService $emailService,
        NotificationService $notificationService
    ) {
        $this->emailService = $emailService;
        $this->notificationService = $notificationService;
    }

    /**
     * Handle incoming email webhook
     */
    public function handle(Request $request)
    {
        try {
            Log::info('Mailbox webhook received', $request->all());

            // Validate webhook signature if needed
            if (!$this->validateWebhook($request)) {
                return response()->json(['error' => 'Invalid webhook signature'], 401);
            }

            // Process the email
            $this->processIncomingEmail($request->all());

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Mailbox webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Process incoming email data
     */
    protected function processIncomingEmail($data)
    {
        try {
            // Get manager user
            $manager = User::whereIn('role', ['admin', 'manager'])->first();
            if (!$manager) {
                Log::error('No manager user found for webhook processing');
                return;
            }

            // Create email record
            $email = Email::create([
                'message_id' => $data['message_id'] ?? uniqid(),
                'from_email' => $data['from_email'] ?? '',
                'to_emails' => json_encode($data['to_emails'] ?? []),
                'cc_emails' => json_encode($data['cc_emails'] ?? []),
                'subject' => $data['subject'] ?? 'No Subject',
                'body' => $data['body'] ?? '',
                'received_at' => $data['received_at'] ?? now(),
                'has_attachments' => $data['has_attachments'] ?? false,
                'user_id' => $manager->id,
            ]);

            // Create notifications
            $this->notificationService->createNewEmailNotification($email);

            Log::info("Webhook processed email: {$email->subject} from {$email->from_email}");

        } catch (\Exception $e) {
            Log::error('Error processing webhook email: ' . $e->getMessage());
        }
    }

    /**
     * Validate webhook signature
     */
    protected function validateWebhook(Request $request)
    {
        $secret = config('mailbox.webhook.secret');
        if (!$secret) {
            return true; // No secret configured, allow all
        }

        $signature = $request->header('X-Webhook-Signature');
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }
}
