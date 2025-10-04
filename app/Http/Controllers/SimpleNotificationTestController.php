<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\EmailNotification;
use Illuminate\Http\Request;

class SimpleNotificationTestController extends Controller
{
    public function createNotification()
    {
        try {
            // Get any recent email or create a test one
            $email = Email::where('email_type', 'sent')->first();

            if (!$email) {
                // Create a test email if none exists
                $email = Email::create([
                    'from_email' => 'test@orion-contracting.com',
                    'to_email' => 'client@example.com',
                    'subject' => 'Test Email Subject',
                    'body' => 'This is a test email',
                    'email_type' => 'sent',
                    'status' => 'sent',
                    'sent_at' => now(),
                    'is_tracked' => true,
                    'user_id' => 1, // Assuming user ID 1 exists
                ]);
            }

            // Create notification for original sender
            $notification = EmailNotification::create([
                'user_id' => $email->user_id ?? 1,
                'email_id' => $email->id,
                'notification_type' => 'reply_received',
                'message' => "You received a reply from engineering@orion-contracting.com regarding: {$email->subject}",
                'is_read' => false,
            ]);

            // ALSO create notification for manager (User ID 1)
            $manager = User::find(1);
            if ($manager && $manager->id !== ($email->user_id ?? 1)) {
                $managerNotification = EmailNotification::create([
                    'user_id' => $manager->id,
                    'email_id' => $email->id,
                    'notification_type' => 'reply_received',
                    'message' => "Reply received from engineering@orion-contracting.com for email: {$email->subject}",
                    'is_read' => false,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification created successfully!',
                'notification_id' => $notification->id,
                'email_id' => $email->id,
                'user_id' => $notification->user_id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function checkNotifications()
    {
        try {
            $total = EmailNotification::count();
            $unread = EmailNotification::where('is_read', false)->count();
            $recent = EmailNotification::orderBy('created_at', 'desc')->limit(5)->get();

            return response()->json([
                'success' => true,
                'total_notifications' => $total,
                'unread_notifications' => $unread,
                'recent_notifications' => $recent
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
