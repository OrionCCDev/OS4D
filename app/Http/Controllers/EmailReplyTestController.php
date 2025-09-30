<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\EmailNotification;
use App\Services\SimpleEmailTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailReplyTestController extends Controller
{
    protected $emailTrackingService;

    public function __construct(SimpleEmailTrackingService $emailTrackingService)
    {
        $this->emailTrackingService = $emailTrackingService;
    }

    /**
     * Test endpoint to simulate a reply to a specific email
     */
    public function simulateReply(Request $request)
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
                'body' => 'This is a test reply generated at ' . now()->toISOString() . '. This simulates someone replying to your email.',
                'message_id' => 'test-reply-' . time(),
            ];

            Log::info('Simulating reply for email ID: ' . $emailId, $replyData);

            // Process the reply
            $success = $this->emailTrackingService->handleIncomingReply($replyData);

            if ($success) {
                // Check if notification was created
                $notification = EmailNotification::where('email_id', $emailId)
                    ->where('notification_type', 'reply_received')
                    ->latest()
                    ->first();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Test reply processed successfully',
                    'email_id' => $emailId,
                    'reply_data' => $replyData,
                    'notification_created' => $notification ? true : false,
                    'notification_id' => $notification ? $notification->id : null
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to process test reply'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error in simulate reply: ' . $e->getMessage());
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
                ->get(['id', 'subject', 'from_email', 'to_email', 'sent_at', 'gmail_message_id']);

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

    /**
     * Check for replies on all recent emails
     */
    public function checkAllReplies()
    {
        try {
            $result = $this->emailTrackingService->checkForReplies();
            
            return response()->json([
                'status' => 'success',
                'result' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking all replies: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStats()
    {
        try {
            $totalNotifications = EmailNotification::count();
            $unreadNotifications = EmailNotification::where('is_read', false)->count();
            $replyNotifications = EmailNotification::where('notification_type', 'reply_received')->count();
            
            $recentNotifications = EmailNotification::with(['email'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'status' => 'success',
                'stats' => [
                    'total_notifications' => $totalNotifications,
                    'unread_notifications' => $unreadNotifications,
                    'reply_notifications' => $replyNotifications,
                ],
                'recent_notifications' => $recentNotifications
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting notification stats: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
