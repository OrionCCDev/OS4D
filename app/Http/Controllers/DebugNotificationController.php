<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\EmailNotification;
use Illuminate\Http\Request;

class DebugNotificationController extends Controller
{
    public function createNotificationForUser(Request $request)
    {
        try {
            $userId = $request->input('user_id');

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID is required'
                ]);
            }

            // Get or create a test email
            $email = Email::where('email_type', 'sent')->first();

            if (!$email) {
                $email = Email::create([
                    'from_email' => 'test@orion-contracting.com',
                    'to_email' => 'client@example.com',
                    'subject' => 'Test Email Subject',
                    'body' => 'This is a test email',
                    'email_type' => 'sent',
                    'status' => 'sent',
                    'sent_at' => now(),
                    'is_tracked' => true,
                    'user_id' => $userId,
                ]);
            }

            // Create notification for specific user
            $notification = EmailNotification::create([
                'user_id' => $userId,
                'email_id' => $email->id,
                'notification_type' => 'reply_received',
                'message' => "You received a reply from engineering@orion-contracting.com regarding: {$email->subject}",
                'is_read' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification created successfully!',
                'notification_id' => $notification->id,
                'user_id' => $userId,
                'email_id' => $email->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
