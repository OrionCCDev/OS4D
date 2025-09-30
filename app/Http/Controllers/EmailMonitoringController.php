<?php

namespace App\Http\Controllers;

use App\Services\EmailMonitoringService;
use App\Models\Email;
use App\Models\EmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmailMonitoringController extends Controller
{
    protected $emailMonitoringService;

    public function __construct(EmailMonitoringService $emailMonitoringService)
    {
        $this->emailMonitoringService = $emailMonitoringService;
    }

    /**
     * Display email monitoring dashboard
     */
    public function index()
    {
        $stats = $this->emailMonitoringService->getMonitoringStats();
        
        $recentEmails = Email::where('user_id', Auth::id())
            ->where('email_type', 'sent')
            ->where('is_tracked', true)
            ->with(['replies', 'task'])
            ->orderBy('sent_at', 'desc')
            ->limit(10)
            ->get();

        $recentNotifications = EmailNotification::where('user_id', Auth::id())
            ->where('notification_type', 'reply_received')
            ->with(['email'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('emails.monitoring-dashboard', compact('stats', 'recentEmails', 'recentNotifications'));
    }

    /**
     * Get monitoring statistics via API
     */
    public function getStats()
    {
        try {
            $stats = $this->emailMonitoringService->getMonitoringStats();
            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Error getting monitoring stats: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Manually trigger email monitoring
     */
    public function triggerMonitoring()
    {
        try {
            $results = $this->emailMonitoringService->monitorForReplies();
            
            return response()->json([
                'success' => true,
                'message' => 'Email monitoring completed',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Error triggering email monitoring: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get setup instructions for email provider
     */
    public function getProviderSetup(Request $request)
    {
        $provider = $request->input('provider');
        
        if (!$provider) {
            return response()->json(['error' => 'Provider parameter is required'], 400);
        }

        try {
            $setupInstructions = $this->emailMonitoringService->setupEmailProviderMonitoring($provider);
            return response()->json($setupInstructions);
        } catch (\Exception $e) {
            Log::error('Error getting provider setup: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get user's email notifications
     */
    public function getNotifications()
    {
        try {
            $notifications = EmailNotification::where('user_id', Auth::id())
                ->with(['email.task'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json($notifications);
        } catch (\Exception $e) {
            Log::error('Error getting notifications: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead(Request $request, $id)
    {
        try {
            $notification = EmailNotification::where('user_id', Auth::id())
                ->where('id', $id)
                ->first();

            if (!$notification) {
                return response()->json(['error' => 'Notification not found'], 404);
            }

            $notification->markAsRead();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsAsRead()
    {
        try {
            EmailNotification::where('user_id', Auth::id())
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get unread notification count
     */
    public function getUnreadCount()
    {
        try {
            $count = EmailNotification::where('user_id', Auth::id())
                ->where('is_read', false)
                ->count();

            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            Log::error('Error getting unread count: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Test webhook endpoint
     */
    public function testWebhook(Request $request)
    {
        try {
            Log::info('Test webhook received', $request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Webhook test successful',
                'timestamp' => now()->toISOString(),
                'received_data' => $request->all()
            ]);
        } catch (\Exception $e) {
            Log::error('Test webhook failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
