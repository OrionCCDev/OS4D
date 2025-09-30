<?php

namespace App\Http\Controllers;

use App\Services\LiveEmailMonitoringService;
use App\Models\Email;
use App\Models\EmailNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LiveEmailMonitoringController extends Controller
{
    protected $monitoringService;

    public function __construct(LiveEmailMonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    /**
     * Show live monitoring dashboard
     */
    public function index()
    {
        $user = Auth::user();

        // Get live stats
        $stats = $this->monitoringService->getLiveStats();

        // Get recent emails
        $recentEmails = Email::where('email_type', 'received')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Get user's notifications
        $userNotifications = EmailNotification::where('user_id', $user->id)
            ->with('email')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get unread count
        $unreadCount = EmailNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return view('emails.live-monitoring', compact(
            'stats',
            'recentEmails',
            'userNotifications',
            'unreadCount'
        ));
    }

    /**
     * Get live email statistics
     */
    public function getStats()
    {
        $stats = $this->monitoringService->getLiveStats();

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Trigger live monitoring manually
     */
    public function triggerMonitoring()
    {
        try {
            $results = $this->monitoringService->monitorInbox();

            return response()->json([
                'success' => true,
                'message' => 'Live monitoring completed successfully',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Live monitoring error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error during monitoring: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get live notifications for current user
     */
    public function getLiveNotifications()
    {
        $user = Auth::user();

        $notifications = EmailNotification::where('user_id', $user->id)
            ->with('email')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $unreadCount = EmailNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'count' => $unreadCount,
            'data' => $notifications,
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        try {
            $notification = EmailNotification::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if ($notification) {
                $notification->update(['is_read' => true]);

                return response()->json([
                    'success' => true,
                    'message' => 'Notification marked as read'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking notification as read: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        try {
            EmailNotification::where('user_id', Auth::id())
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking notifications as read: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread count
     */
    public function getUnreadCount()
    {
        $count = EmailNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Create test notifications
     */
    public function createTestNotifications()
    {
        try {
            $user = Auth::user();

            // Create test email
            $email = Email::create([
                'user_id' => $user->id,
                'from_email' => 'test@example.com',
                'to_email' => $user->email,
                'subject' => 'Test Live Email - ' . now()->format('H:i:s'),
                'body' => 'This is a test email for live monitoring system.',
                'email_type' => 'received',
                'status' => 'received',
                'is_tracked' => false,
                'received_at' => now(),
                'gmail_message_id' => 'test_live_' . uniqid(),
            ]);

            // Create notification
            $notification = EmailNotification::create([
                'user_id' => $user->id,
                'email_id' => $email->id,
                'notification_type' => 'email_received',
                'message' => "Test live email received from test@example.com: Test Live Email - " . now()->format('H:i:s'),
                'is_read' => false,
            ]);

            // Also create for manager
            $manager = User::find(1);
            if ($manager && $manager->id !== $user->id) {
                EmailNotification::create([
                    'user_id' => $manager->id,
                    'email_id' => $email->id,
                    'notification_type' => 'email_received',
                    'message' => "Test live email received for {$user->name}: Test Live Email - " . now()->format('H:i:s'),
                    'is_read' => false,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Test notifications created successfully!',
                'notification_id' => $notification->id,
                'email_id' => $email->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating test notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all emails for manager view
     */
    public function getAllEmails()
    {
        $user = Auth::user();

        // Only managers can see all emails
        if (!$user->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Manager privileges required.'
            ], 403);
        }

        $emails = Email::where('email_type', 'received')
            ->with(['user', 'notifications'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'emails' => $emails
        ]);
    }

    /**
     * Get email details
     */
    public function getEmailDetails($id)
    {
        $email = Email::with(['user', 'notifications', 'replies'])
            ->find($id);

        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'Email not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'email' => $email
        ]);
    }
}
