<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\EmailNotification;
use App\Services\SimpleEmailTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailReplyWebhookController extends Controller
{
    protected $emailTrackingService;

    public function __construct(SimpleEmailTrackingService $emailTrackingService)
    {
        $this->emailTrackingService = $emailTrackingService;
    }

    /**
     * Handle incoming email reply webhook
     * This endpoint can be called manually or by email service providers
     */
    public function handleReply(Request $request)
    {
        try {
            Log::info('=== EMAIL REPLY WEBHOOK RECEIVED ===');
            Log::info('Request method: ' . $request->method());
            Log::info('Request headers: ', $request->headers->all());
            Log::info('Request body: ', $request->all());

            // Parse email data from request
            $emailData = $this->parseEmailData($request);
            Log::info('Parsed email data: ', $emailData);

            // Process the reply
            $success = $this->emailTrackingService->handleIncomingReply($emailData);
            Log::info('Reply processing result: ' . ($success ? 'success' : 'failed'));

            if ($success) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Reply processed successfully',
                    'processed_at' => now()->toISOString()
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Could not process reply'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error handling email reply webhook: ' . $e->getMessage());
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
                'to' => $request->input('to'),
                'subject' => $request->input('subject'),
                'body' => $request->input('body', ''),
                'message_id' => $request->input('message_id'),
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
                'message_id' => $jsonData['message_id'] ?? null,
            ];
        }

        // Format 3: Form data
        return [
            'from' => $request->input('from', 'unknown@example.com'),
            'to' => $request->input('to', 'designers@orion-contracting.com'),
            'subject' => $request->input('subject', 'No Subject'),
            'body' => $request->input('body', ''),
            'message_id' => $request->input('message_id'),
        ];
    }

    /**
     * Test endpoint to simulate a reply
     */
    public function testReply(Request $request)
    {
        try {
            $emailId = $request->input('email_id');

            if (!$emailId) {
                return response()->json(['error' => 'email_id is required'], 400);
            }

            $email = Email::find($emailId);
            if (!$email) {
                return response()->json(['error' => 'Email not found'], 404);
            }

            // Create test reply data
            $replyData = [
                'from' => 'test-reply@example.com',
                'to' => $email->from_email,
                'subject' => 'Re: ' . $email->subject,
                'body' => 'This is a test reply generated at ' . now()->toISOString(),
                'message_id' => 'test-reply-' . time(),
            ];

            // Process the reply
            $success = $this->emailTrackingService->handleIncomingReply($replyData);

            if ($success) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Test reply processed successfully',
                    'email_id' => $emailId,
                    'reply_data' => $replyData
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to process test reply'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error in test reply: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent sent emails for testing
     */
    public function getRecentEmails()
    {
        try {
            $emails = Email::where('email_type', 'sent')
                ->where('is_tracked', true)
                ->whereNull('replied_at')
                ->where('sent_at', '>=', now()->subDays(7))
                ->orderBy('sent_at', 'desc')
                ->limit(10)
                ->get(['id', 'subject', 'from_email', 'to_email', 'sent_at']);

            return response()->json([
                'status' => 'success',
                'emails' => $emails
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting recent emails: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
