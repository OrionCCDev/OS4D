<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;
use App\Models\UnifiedNotification;
use App\Events\NotificationRead;
use App\Events\NotificationCountUpdated;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get all notifications for the authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $category = $request->get('category'); // 'task' or 'email'
        $limit = $request->get('limit', 50);

        $notifications = $this->notificationService->getUserNotifications($user->id, $category, $limit);
        $stats = $this->notificationService->getNotificationStats($user->id);

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'stats' => $stats
        ]);
    }

    /**
     * Get task notifications only
     */
    public function taskNotifications(Request $request)
    {
        $user = Auth::user();
        $limit = $request->get('limit', 50);

        $notifications = $this->notificationService->getUserNotifications($user->id, 'task', $limit);
        $stats = $this->notificationService->getNotificationStats($user->id);

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'stats' => [
                'total' => $stats['task_unread'],
                'unread' => $stats['task_unread']
            ]
        ]);
    }

    /**
     * Get email notifications only
     */
    public function emailNotifications(Request $request)
    {
        $user = Auth::user();
        $limit = $request->get('limit', 50);

        $notifications = $this->notificationService->getUserNotifications($user->id, 'email', $limit);
        $stats = $this->notificationService->getNotificationStats($user->id);

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'stats' => [
                'total' => $stats['email_unread'],
                'unread' => $stats['email_unread']
            ]
        ]);
    }

    /**
     * Get notification statistics
     */
    public function stats()
    {
        $user = Auth::user();
        $stats = $this->notificationService->getNotificationStats($user->id);

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Get unread notification count
     */
    public function unreadCount()
    {
        $user = Auth::user();
        $stats = $this->notificationService->getNotificationStats($user->id);

        return response()->json([
            'success' => true,
            'counts' => [
                'total' => $stats['unread'],
                'task' => $stats['task_unread'],
                'email' => $stats['email_unread']
            ]
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $user = Auth::user();
        $success = $this->notificationService->markAsRead($id, $user->id);

        if ($success) {
            // Get updated counts
            $counts = $this->notificationService->getNotificationCounts($user->id);

            // Broadcast the read event
            broadcast(new NotificationRead($id, $user->id, $counts))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
                'counts' => $counts
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notification not found'
        ], 404);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        $category = $request->get('category'); // 'task' or 'email'

        $count = $this->notificationService->markAllAsRead($user->id, $category);

        // Get updated counts
        $counts = $this->notificationService->getNotificationCounts($user->id);

        // Broadcast the count update
        broadcast(new NotificationCountUpdated($user->id, $counts))->toOthers();

        return response()->json([
            'success' => true,
            'message' => "Marked {$count} notifications as read",
            'counts' => $counts
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $success = $this->notificationService->delete($id, $user->id);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notification not found'
        ], 404);
    }
}
