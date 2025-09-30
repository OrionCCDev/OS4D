<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\EmailNotification;
use Illuminate\Http\Request;

class EmailDebugController extends Controller
{
    public function debug()
    {
        try {
            $totalEmails = Email::count();
            $sentEmails = Email::where('email_type', 'sent')->count();
            $receivedEmails = Email::where('email_type', 'received')->count();
            $trackedEmails = Email::where('is_tracked', true)->count();
            
            $recentSentEmails = Email::where('email_type', 'sent')
                ->orderBy('sent_at', 'desc')
                ->limit(5)
                ->get(['id', 'subject', 'sent_at', 'is_tracked', 'replied_at']);
            
            $totalNotifications = EmailNotification::count();
            $unreadNotifications = EmailNotification::where('is_read', false)->count();
            $replyNotifications = EmailNotification::where('notification_type', 'reply_received')->count();
            
            $recentNotifications = EmailNotification::with(['email'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'status' => 'success',
                'email_stats' => [
                    'total_emails' => $totalEmails,
                    'sent_emails' => $sentEmails,
                    'received_emails' => $receivedEmails,
                    'tracked_emails' => $trackedEmails,
                ],
                'notification_stats' => [
                    'total_notifications' => $totalNotifications,
                    'unread_notifications' => $unreadNotifications,
                    'reply_notifications' => $replyNotifications,
                ],
                'recent_sent_emails' => $recentSentEmails,
                'recent_notifications' => $recentNotifications
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
