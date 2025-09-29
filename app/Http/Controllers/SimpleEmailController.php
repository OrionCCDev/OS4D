<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\User;
use App\Services\SimpleEmailTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SimpleEmailController extends Controller
{
    protected $emailTrackingService;

    public function __construct(SimpleEmailTrackingService $emailTrackingService)
    {
        $this->emailTrackingService = $emailTrackingService;
    }

    /**
     * Handle incoming email webhook from designers@orion-contracting.com
     * This endpoint receives replies and processes them
     */
    public function handleIncomingEmail(Request $request)
    {
        try {
            Log::info('Incoming email webhook received', $request->all());

            // Parse email data from webhook
            $emailData = $this->parseEmailData($request);

            // Process the reply
            $success = $this->emailTrackingService->handleIncomingReply($emailData);

            if ($success) {
                return response()->json(['status' => 'success', 'message' => 'Reply processed successfully']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Could not process reply'], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error handling incoming email: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Parse email data from webhook request
     * This depends on your email service provider (SendGrid, Mailgun, etc.)
     */
    private function parseEmailData(Request $request): array
    {
        // Example for SendGrid webhook format
        if ($request->has('envelope')) {
            $envelope = $request->input('envelope');
            return [
                'from' => $envelope['from'] ?? 'unknown@example.com',
                'to' => $envelope['to'][0] ?? 'designers@orion-contracting.com',
                'subject' => $request->input('subject', 'No Subject'),
                'body' => $request->input('text', '') ?: $request->input('html', ''),
                'message_id' => $request->input('message_id'),
            ];
        }

        // Example for Mailgun webhook format
        if ($request->has('sender')) {
            return [
                'from' => $request->input('sender'),
                'to' => $request->input('recipient'),
                'subject' => $request->input('subject', 'No Subject'),
                'body' => $request->input('body-plain', '') ?: $request->input('body-html', ''),
                'message_id' => $request->input('Message-Id'),
            ];
        }

        // Generic format
        return [
            'from' => $request->input('from', 'unknown@example.com'),
            'to' => $request->input('to', 'designers@orion-contracting.com'),
            'subject' => $request->input('subject', 'No Subject'),
            'body' => $request->input('body', ''),
            'message_id' => $request->input('message_id'),
        ];
    }

    /**
     * Manual check for replies (for testing)
     */
    public function checkReplies()
    {
        try {
            $result = $this->emailTrackingService->checkForReplies();

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'] ?? 'Check completed',
                'replies_found' => count($result['replies'] ?? []),
                'replies' => $result['replies'] ?? []
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking replies: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get email statistics for current user
     */
    public function getStats()
    {
        try {
            $stats = $this->emailTrackingService->getEmailStats(Auth::user());
            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Error getting email stats: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * List sent emails for current user
     */
    public function listSentEmails()
    {
        try {
            $emails = Email::where('user_id', Auth::id())
                ->where('email_type', 'sent')
                ->where('is_tracked', true)
                ->with(['replies', 'task'])
                ->orderBy('sent_at', 'desc')
                ->paginate(20);

            return response()->json($emails);
        } catch (\Exception $e) {
            Log::error('Error listing sent emails: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show email details with replies
     */
    public function showEmail($id)
    {
        try {
            $email = Email::where('id', $id)
                ->where('user_id', Auth::id())
                ->with(['replies', 'task'])
                ->first();

            if (!$email) {
                return response()->json(['error' => 'Email not found'], 404);
            }

            return response()->json($email);
        } catch (\Exception $e) {
            Log::error('Error showing email: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
