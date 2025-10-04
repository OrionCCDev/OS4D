<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\EmailNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LiveEmailTestController extends Controller
{
    /**
     * Create a test reply notification immediately
     */
    public function createTestReply(Request $request)
    {
        try {
            // Get the most recent sent email
            $recentEmail = Email::where('email_type', 'sent')
                ->where('is_tracked', true)
                ->orderBy('sent_at', 'desc')
                ->first();

            if (!$recentEmail) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No recent sent emails found. Send an email first.'
                ], 400);
            }

            // Create a test reply email
            $replyEmail = Email::create([
                'from_email' => 'test-reply@example.com',
                'to_email' => $recentEmail->from_email,
                'subject' => 'Re: ' . $recentEmail->subject,
                'body' => 'This is a test reply created at ' . now()->toISOString() . '. This simulates someone replying to your email.',
                'email_type' => 'received',
                'status' => 'received',
                'received_at' => now(),
                'message_id' => 'test-reply-' . time(),
                'is_tracked' => true,
                'reply_to_email_id' => $recentEmail->id,
                'user_id' => $recentEmail->user_id,
                'task_id' => $recentEmail->task_id,
            ]);

            // Mark original email as replied
            $recentEmail->update(['replied_at' => now()]);

            // Create notification
            $notification = EmailNotification::create([
                'user_id' => $recentEmail->user_id,
                'email_id' => $recentEmail->id,
                'notification_type' => 'reply_received',
                'message' => "You received a reply from test-reply@example.com regarding: {$recentEmail->subject}",
                'is_read' => false,
            ]);

            Log::info('Test reply notification created', [
                'original_email_id' => $recentEmail->id,
                'reply_email_id' => $replyEmail->id,
                'notification_id' => $notification->id,
                'user_id' => $recentEmail->user_id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Test reply notification created successfully!',
                'data' => [
                    'original_email_id' => $recentEmail->id,
                    'reply_email_id' => $replyEmail->id,
                    'notification_id' => $notification->id,
                    'user_id' => $recentEmail->user_id,
                    'original_subject' => $recentEmail->subject,
                    'reply_subject' => $replyEmail->subject
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating test reply: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current notification status
     */
    public function getNotificationStatus()
    {
        try {
            $totalNotifications = EmailNotification::count();
            $unreadNotifications = EmailNotification::where('is_read', false)->count();
            $replyNotifications = EmailNotification::where('notification_type', 'reply_received')->count();

            $recentNotifications = EmailNotification::with(['email'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $recentEmails = Email::where('email_type', 'sent')
                ->where('is_tracked', true)
                ->orderBy('sent_at', 'desc')
                ->limit(5)
                ->get(['id', 'subject', 'sent_at', 'replied_at']);

            return response()->json([
                'status' => 'success',
                'stats' => [
                    'total_notifications' => $totalNotifications,
                    'unread_notifications' => $unreadNotifications,
                    'reply_notifications' => $replyNotifications,
                ],
                'recent_notifications' => $recentNotifications,
                'recent_emails' => $recentEmails
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting notification status: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simulate a real email reply from engineering@orion-contracting.com
     */
    public function simulateDesignersReply(Request $request)
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

            // Create a reply email as if it came from engineering@orion-contracting.com
            $replyEmail = Email::create([
                'from_email' => 'engineering@orion-contracting.com',
                'to_email' => $email->from_email,
                'subject' => 'Re: ' . $email->subject,
                'body' => 'This is a simulated reply from engineering@orion-contracting.com at ' . now()->toISOString() . '. This simulates someone replying to your email.',
                'email_type' => 'received',
                'status' => 'received',
                'received_at' => now(),
                'message_id' => 'designers-reply-' . time(),
                'is_tracked' => true,
                'reply_to_email_id' => $email->id,
                'user_id' => $email->user_id,
                'task_id' => $email->task_id,
            ]);

            // Mark original email as replied
            $email->update(['replied_at' => now()]);

            // Create notification
            $notification = EmailNotification::create([
                'user_id' => $email->user_id,
                'email_id' => $email->id,
                'notification_type' => 'reply_received',
                'message' => "You received a reply from engineering@orion-contracting.com regarding: {$email->subject}",
                'is_read' => false,
            ]);

            Log::info('Designers reply notification created', [
                'original_email_id' => $email->id,
                'reply_email_id' => $replyEmail->id,
                'notification_id' => $notification->id,
                'user_id' => $email->user_id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Designers reply notification created successfully!',
                'data' => [
                    'original_email_id' => $email->id,
                    'reply_email_id' => $replyEmail->id,
                    'notification_id' => $notification->id,
                    'user_id' => $email->user_id,
                    'original_subject' => $email->subject,
                    'reply_subject' => $replyEmail->subject
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating designers reply: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
