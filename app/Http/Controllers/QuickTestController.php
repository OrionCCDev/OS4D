<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\EmailNotification;
use Illuminate\Http\Request;

class QuickTestController extends Controller
{
    public function quickTest()
    {
        try {
            // Get recent sent email
            $recentEmail = Email::where('email_type', 'sent')
                ->where('is_tracked', true)
                ->orderBy('sent_at', 'desc')
                ->first();

            if (!$recentEmail) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No recent sent emails found. Send an email first.'
                ]);
            }

            // Create a test reply notification
            $notification = EmailNotification::create([
                'user_id' => $recentEmail->user_id,
                'email_id' => $recentEmail->id,
                'notification_type' => 'reply_received',
                'message' => "You received a reply from test@example.com regarding: {$recentEmail->subject}",
                'is_read' => false,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Test notification created!',
                'notification_id' => $notification->id,
                'user_id' => $recentEmail->user_id,
                'email_subject' => $recentEmail->subject
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
