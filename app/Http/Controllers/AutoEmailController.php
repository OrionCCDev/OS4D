<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AutoEmailFetchService;
use App\Models\DesignersInboxNotification;
use Illuminate\Support\Facades\Log;

class AutoEmailController extends Controller
{
    protected $autoEmailService;

    public function __construct(AutoEmailFetchService $autoEmailService)
    {
        $this->autoEmailService = $autoEmailService;
    }

    /**
     * Automatically fetch and process emails
     */
    public function autoFetch(Request $request)
    {
        try {
            Log::info('AutoEmailController: Starting automatic email fetch');

            $result = $this->autoEmailService->autoFetchAndProcess();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => [
                        'fetched' => $result['fetched'],
                        'stored' => $result['stored'],
                        'skipped' => $result['skipped'],
                        'notifications_created' => $result['notifications_created']
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch emails automatically',
                    'errors' => $result['errors']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('AutoEmailController: Exception in autoFetch: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching emails',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread notifications count for navigation badge
     */
    public function getUnreadCount(Request $request)
    {
        try {
            $user = Auth::user();

            // Only managers can see designers inbox notifications
            if (!$user->isManager()) {
                return response()->json([
                    'success' => true,
                    'count' => 0
                ]);
            }

            $count = DesignersInboxNotification::where('user_id', $user->id)
                ->unread()
                ->count();

            return response()->json([
                'success' => true,
                'count' => $count
            ]);

        } catch (\Exception $e) {
            Log::error('AutoEmailController: Error getting unread count: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'count' => 0
            ]);
        }
    }

    /**
     * Get recent notifications for dropdown
     */
    public function getRecentNotifications(Request $request)
    {
        try {
            $user = Auth::user();

            // Only managers can see designers inbox notifications
            if (!$user->isManager()) {
                return response()->json([
                    'success' => true,
                    'notifications' => []
                ]);
            }

            $notifications = DesignersInboxNotification::where('user_id', $user->id)
                ->with('email')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'created_at' => $notification->created_at->diffForHumans(),
                        'is_read' => $notification->isRead(),
                        'email' => $notification->email ? [
                            'id' => $notification->email->id,
                            'subject' => $notification->email->subject,
                            'from_email' => $notification->email->from_email,
                            'received_at' => $notification->email->received_at->format('M d, H:i')
                        ] : null
                    ];
                });

            return response()->json([
                'success' => true,
                'notifications' => $notifications
            ]);

        } catch (\Exception $e) {
            Log::error('AutoEmailController: Error getting recent notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'notifications' => []
            ]);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            $user = Auth::user();

            $notification = DesignersInboxNotification::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ], 404);
            }

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);

        } catch (\Exception $e) {
            Log::error('AutoEmailController: Error marking notification as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error marking notification as read'
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $user = Auth::user();

            $updated = DesignersInboxNotification::where('user_id', $user->id)
                ->unread()
                ->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => "Marked {$updated} notifications as read"
            ]);

        } catch (\Exception $e) {
            Log::error('AutoEmailController: Error marking all notifications as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error marking notifications as read'
            ], 500);
        }
    }

    /**
     * Get fetch statistics
     */
    public function getStatistics(Request $request)
    {
        try {
            $stats = $this->autoEmailService->getFetchStatistics();

            return response()->json([
                'success' => true,
                'statistics' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('AutoEmailController: Error getting statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting statistics'
            ], 500);
        }
    }
}
