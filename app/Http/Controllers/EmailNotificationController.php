<?php

namespace App\Http\Controllers;

use App\Models\EmailNotification;
use App\Models\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailNotificationController extends Controller
{
    /**
     * Display a listing of email notifications
     */
    public function index()
    {
        $notifications = EmailNotification::where('user_id', Auth::id())
            ->with(['email'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('notifications.email-notifications', compact('notifications'));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = EmailNotification::where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        EmailNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * Get unread notification count
     */
    public function getUnreadCount()
    {
        $count = EmailNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get email statistics
     */
    public function getEmailStats()
    {
        $emailTrackingService = app(\App\Services\EmailTrackingService::class);
        $stats = $emailTrackingService->getEmailStats(Auth::user());

        return response()->json($stats);
    }

    /**
     * Show email details
     */
    public function showEmail($id)
    {
        $email = Email::where('id', $id)
            ->where('user_id', Auth::id())
            ->with(['task', 'replies'])
            ->first();

        if (!$email) {
            abort(404);
        }

        return view('emails.show', compact('email'));
    }
}
