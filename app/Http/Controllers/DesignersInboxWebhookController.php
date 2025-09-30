<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\EmailNotification;
use App\Models\User;
use App\Services\DesignersInboxMonitorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DesignersInboxWebhookController extends Controller
{
    protected $monitorService;

    public function __construct(DesignersInboxMonitorService $monitorService)
    {
        $this->monitorService = $monitorService;
    }

    /**
     * Handle incoming email webhook from email forwarding
     * This endpoint can be called by cPanel email forwarding rules
     */
    public function handleIncomingEmail(Request $request)
    {
        try {
            Log::info('=== DESIGNERS INBOX WEBHOOK RECEIVED ===');
            Log::info('Request method: ' . $request->method());
            Log::info('Request headers: ', $request->headers->all());
            Log::info('Request body: ', $request->all());

            // Parse email data from request
            $emailData = $this->parseEmailData($request);
            Log::info('Parsed email data: ', $emailData);

            // Process the email
            $result = $this->processIncomingEmail($emailData);

            if ($result) {
                Log::info('Email processed successfully', $result);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Email processed successfully',
                    'processed_at' => now()->toISOString(),
                    'result' => $result
                ]);
            } else {
                Log::warning('Email processing failed');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Could not process email'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error handling designers inbox webhook: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse email data from webhook request
     */
    private function parseEmailData(Request $request): array
    {
        // Handle different webhook formats

        // Format 1: Direct POST data
        if ($request->has('from') && $request->has('subject')) {
            return [
                'from' => $request->input('from'),
                'to' => $request->input('to', 'designers@orion-contracting.com'),
                'subject' => $request->input('subject'),
                'body' => $request->input('body', ''),
                'message_id' => $request->input('message_id', 'webhook-' . time()),
                'date' => $request->input('date', now()->toISOString()),
            ];
        }

        // Format 2: JSON payload
        $jsonData = $request->json()->all();
        if (!empty($jsonData)) {
            return [
                'from' => $jsonData['from'] ?? 'unknown@example.com',
                'to' => $jsonData['to'] ?? 'designers@orion-contracting.com',
                'subject' => $jsonData['subject'] ?? 'No Subject',
                'body' => $jsonData['body'] ?? '',
                'message_id' => $jsonData['message_id'] ?? 'webhook-' . time(),
                'date' => $jsonData['date'] ?? now()->toISOString(),
            ];
        }

        // Format 3: Form data
        return [
            'from' => $request->input('from', 'unknown@example.com'),
            'to' => $request->input('to', 'designers@orion-contracting.com'),
            'subject' => $request->input('subject', 'No Subject'),
            'body' => $request->input('body', ''),
            'message_id' => $request->input('message_id', 'webhook-' . time()),
            'date' => $request->input('date', now()->toISOString()),
        ];
    }

    /**
     * Process incoming email and create notifications
     */
    private function processIncomingEmail(array $emailData): ?array
    {
        try {
            // Check if this email is already processed
            $existingEmail = Email::where('message_id', $emailData['message_id'])->first();
            if ($existingEmail) {
                Log::info('Email already processed: ' . $emailData['message_id']);
                return null;
            }

            // Create email record
            $email = Email::create([
                'from_email' => $emailData['from'],
                'to_email' => $emailData['to'],
                'subject' => $emailData['subject'],
                'body' => $emailData['body'],
                'email_type' => 'received',
                'status' => 'received',
                'received_at' => now(),
                'message_id' => $emailData['message_id'],
                'is_tracked' => true,
            ]);

            // Try to find original email this is replying to
            $originalEmail = $this->findOriginalEmail($emailData);

            if ($originalEmail) {
                // This is a reply to an existing email
                $email->update([
                    'reply_to_email_id' => $originalEmail->id,
                    'user_id' => $originalEmail->user_id,
                    'task_id' => $originalEmail->task_id,
                ]);

                // Mark original email as replied
                $originalEmail->update(['replied_at' => now()]);

                // Create notification
                $this->createReplyNotification($originalEmail, $email);

                Log::info('Reply processed for email ID: ' . $originalEmail->id);

                return [
                    'type' => 'reply',
                    'original_email_id' => $originalEmail->id,
                    'reply_email_id' => $email->id,
                    'from' => $emailData['from'],
                    'subject' => $emailData['subject']
                ];
            } else {
                // This is a new email (not a reply)
                Log::info('New email received: ' . $emailData['subject']);

                return [
                    'type' => 'new_email',
                    'email_id' => $email->id,
                    'from' => $emailData['from'],
                    'subject' => $emailData['subject']
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error processing incoming email: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Find original email this is replying to
     */
    private function findOriginalEmail(array $emailData): ?Email
    {
        try {
            // Try to find by subject (remove "Re:" prefix)
            $originalSubject = preg_replace('/^(Re:|RE:|Fwd:|FWD:)\s*/i', '', $emailData['subject']);

            $email = Email::where('email_type', 'sent')
                ->where('is_tracked', true)
                ->where('subject', 'LIKE', '%' . $originalSubject . '%')
                ->where('sent_at', '>=', now()->subDays(30))
                ->orderBy('sent_at', 'desc')
                ->first();

            return $email;

        } catch (\Exception $e) {
            Log::error('Error finding original email: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create notification for email reply
     */
    private function createReplyNotification(Email $originalEmail, Email $replyEmail): void
    {
        try {
            $user = User::find($originalEmail->user_id);
            if (!$user) {
                Log::warning('User not found for email ID: ' . $originalEmail->id);
                return;
            }

            // Create database notification
            $notification = EmailNotification::create([
                'user_id' => $user->id,
                'email_id' => $originalEmail->id,
                'notification_type' => 'reply_received',
                'message' => "You received a reply from {$replyEmail->from_email} regarding: {$originalEmail->subject}",
                'is_read' => false,
            ]);

            Log::info('Reply notification created for user: ' . $user->id);

        } catch (\Exception $e) {
            Log::error('Error creating reply notification: ' . $e->getMessage());
        }
    }

    /**
     * Test webhook endpoint
     */
    public function testWebhook(Request $request)
    {
        try {
            Log::info('Test webhook received for designers inbox', $request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Designers inbox webhook test successful',
                'timestamp' => now()->toISOString(),
                'data' => $request->all()
            ]);

        } catch (\Exception $e) {
            Log::error('Test webhook error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test IMAP connection
     */
    public function testImapConnection()
    {
        try {
            $result = $this->monitorService->testConnection();

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('IMAP test error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
