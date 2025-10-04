<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFolderController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ContractorController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExternalStakeholderController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\GmailOAuthController;
use App\Http\Controllers\EmailNotificationController;
use App\Services\EmailTrackingService;

// Simple email tracking routes (no auth required for webhooks)
Route::post('/email/webhook/incoming', [App\Http\Controllers\SimpleEmailController::class, 'handleIncomingEmail'])
    ->name('email.webhook.incoming')
    ->withoutMiddleware(['web']); // Exclude CSRF protection for webhooks
Route::get('/email/check-replies', [App\Http\Controllers\SimpleEmailController::class, 'checkReplies'])->name('email.check-replies');

// Redirect root to dashboard (requires authentication)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard route - requires authentication
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Dashboard API routes for charts
Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->middleware(['auth', 'verified'])->name('dashboard.chart-data');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/notification-preferences', [ProfileController::class, 'updateNotificationPreferences'])->name('profile.notification-preferences.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Gmail OAuth routes
    Route::get('/auth/gmail', [GmailOAuthController::class, 'redirect'])->name('gmail.redirect');
    Route::get('/auth/gmail/callback', [GmailOAuthController::class, 'callback'])->name('gmail.callback');
    Route::post('/auth/gmail/disconnect', [GmailOAuthController::class, 'disconnect'])->name('gmail.disconnect');

    // Email notification routes
    Route::get('/email-notifications', [EmailNotificationController::class, 'index'])->name('email-notifications.index');
    Route::post('/email-notifications/{id}/mark-read', [EmailNotificationController::class, 'markAsRead'])->name('email-notifications.mark-read');
    Route::post('/email-notifications/mark-all-read', [EmailNotificationController::class, 'markAllAsRead'])->name('email-notifications.mark-all-read');
    Route::get('/email-notifications/unread-count', [EmailNotificationController::class, 'getUnreadCount'])->name('email-notifications.unread-count');
    Route::get('/email-notifications/stats', [EmailNotificationController::class, 'getEmailStats'])->name('email-notifications.stats');
    Route::get('/emails/sent', [App\Http\Controllers\SimpleEmailController::class, 'listSentEmails'])->name('emails.sent');

    // Email monitoring routes
    Route::get('/email-monitoring', [App\Http\Controllers\EmailMonitoringController::class, 'index'])->name('email-monitoring.index');
    Route::get('/email-monitoring/stats', [App\Http\Controllers\EmailMonitoringController::class, 'getStats'])->name('email-monitoring.stats');
    Route::post('/email-monitoring/trigger', [App\Http\Controllers\EmailMonitoringController::class, 'triggerMonitoring'])->name('email-monitoring.trigger');
    Route::get('/email-monitoring/provider-setup', [App\Http\Controllers\EmailMonitoringController::class, 'getProviderSetup'])->name('email-monitoring.provider-setup');
    Route::get('/email-monitoring/notifications', [App\Http\Controllers\EmailMonitoringController::class, 'getNotifications'])->name('email-monitoring.notifications');
    Route::post('/email-monitoring/notifications/{id}/mark-read', [App\Http\Controllers\EmailMonitoringController::class, 'markNotificationAsRead'])->name('email-monitoring.notifications.mark-read');
    Route::post('/email-monitoring/notifications/mark-all-read', [App\Http\Controllers\EmailMonitoringController::class, 'markAllNotificationsAsRead'])->name('email-monitoring.notifications.mark-all-read');
    Route::get('/email-monitoring/unread-count', [App\Http\Controllers\EmailMonitoringController::class, 'getUnreadCount'])->name('email-monitoring.unread-count');

    // Email reply testing page
    Route::get('/email-test-reply', function () {
        return view('emails.test-reply');
    })->name('email.test-reply');

    // Live email testing page
    Route::get('/live-email-test', function () {
        return view('emails.live-test');
    })->name('live.email-test');

    Route::post('/media/upload', [MediaController::class, 'upload'])->name('media.upload');
    Route::get('/media/list', [MediaController::class, 'list'])->name('media.list');
    Route::post('/media/folder', [MediaController::class, 'makeFolder'])->name('media.folder');

    Route::get('/reports/export-excel', function () {
        $data = [
            ['Name', 'Email'],
            ['User 1', 'user1@example.com'],
            ['User 2', 'user2@example.com'],
        ];

        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromArray {
            public function __construct(private array $data) {}
            public function array(): array
            {
                return $this->data;
            }
        }, 'report.xlsx');
    })->name('reports.excel');

    Route::get('/reports/pdf', function () {
        $html = view('reports.sample', ['title' => 'Sample Report'])->render();
        $pdf = Pdf::loadHTML($html);
        return $pdf->download('report.pdf');
    })->name('reports.pdf');

    // Live Email Monitoring Routes
    Route::get('/live-email-monitoring', [App\Http\Controllers\LiveEmailMonitoringController::class, 'index'])->name('live-monitoring.index');
    Route::get('/live-monitoring/stats', [App\Http\Controllers\LiveEmailMonitoringController::class, 'getStats'])->name('live-monitoring.stats');
    Route::post('/live-monitoring/trigger', [App\Http\Controllers\LiveEmailMonitoringController::class, 'triggerMonitoring'])->name('live-monitoring.trigger');
    Route::get('/live-monitoring/notifications', [App\Http\Controllers\LiveEmailMonitoringController::class, 'getLiveNotifications'])->name('live-monitoring.notifications');
    Route::post('/live-monitoring/notifications/{id}/mark-read', [App\Http\Controllers\LiveEmailMonitoringController::class, 'markAsRead'])->name('live-monitoring.mark-read');
    Route::post('/live-monitoring/notifications/mark-all-read', [App\Http\Controllers\LiveEmailMonitoringController::class, 'markAllAsRead'])->name('live-monitoring.mark-all-read');
    Route::get('/live-monitoring/unread-count', [App\Http\Controllers\LiveEmailMonitoringController::class, 'getUnreadCount'])->name('live-monitoring.unread-count');
    Route::post('/live-monitoring/test', [App\Http\Controllers\LiveEmailMonitoringController::class, 'createTestNotifications'])->name('live-monitoring.test');
    Route::get('/live-monitoring/all-emails', [App\Http\Controllers\LiveEmailMonitoringController::class, 'getAllEmails'])->name('live-monitoring.all-emails');
    Route::get('/live-monitoring/email/{id}', [App\Http\Controllers\LiveEmailMonitoringController::class, 'getEmailDetails'])->name('live-monitoring.email-details');

    // Email Tracker Routes
    Route::get('/email-tracker', [App\Http\Controllers\EmailTrackerController::class, 'index'])->name('email-tracker.index');
    Route::get('/email-tracker/stats', [App\Http\Controllers\EmailTrackerController::class, 'getStats'])->name('email-tracker.stats');
    Route::get('/email-tracker/search', [App\Http\Controllers\EmailTrackerController::class, 'search'])->name('email-tracker.search');
    Route::post('/email-tracker/{id}/mark-read', [App\Http\Controllers\EmailTrackerController::class, 'markAsRead'])->name('email-tracker.mark-read');
    Route::get('/email-tracker/export', [App\Http\Controllers\EmailTrackerController::class, 'export'])->name('email-tracker.export');

    // Admin: Users CRUD
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UsersController::class)->except(['show']);
    });

    // Project workflow resources - Manager onlyst
    Route::middleware('manager')->group(function () {
        Route::resource('projects', ProjectController::class);
        Route::resource('folders', ProjectFolderController::class)->parameters(['folders' => 'folder'])->except(['show']);
        Route::resource('contractors', ContractorController::class)->except(['show']);
        Route::resource('email-templates', EmailTemplateController::class)->parameters(['email-templates' => 'email_template'])->except(['show']);
        Route::resource('external-stakeholders', ExternalStakeholderController::class);
    });

    // Tasks - Restricted access for non-managers
    Route::middleware('task.access')->group(function () {
        Route::resource('tasks', TaskController::class)->except(['destroy']);
        Route::post('tasks/{task}/assign', [TaskController::class, 'assign'])->name('tasks.assign');
        Route::post('tasks/{task}/change-status', [TaskController::class, 'changeStatus'])->name('tasks.change-status');
        Route::post('tasks/{task}/attachments', [TaskController::class, 'uploadAttachment'])->name('tasks.attachments.upload');
        Route::delete('tasks/{task}/attachments/{attachment}', [TaskController::class, 'deleteAttachment'])->name('tasks.attachments.delete');

        // Task workflow routes
        Route::post('tasks/{task}/accept', [TaskController::class, 'acceptTask'])->name('tasks.accept');
        Route::post('tasks/{task}/submit-review', [TaskController::class, 'submitForReview'])->name('tasks.submit-review');
        Route::post('tasks/{task}/approve', [TaskController::class, 'approveTask'])->name('tasks.approve');
        Route::post('tasks/{task}/reject', [TaskController::class, 'rejectTask'])->name('tasks.reject');
        Route::post('tasks/{task}/send-approval-email', [TaskController::class, 'sendApprovalEmail'])->name('tasks.send-approval-email');
        Route::post('tasks/{task}/send-rejection-email', [TaskController::class, 'sendRejectionEmail'])->name('tasks.send-rejection-email');

        // Email preparation routes
        Route::get('tasks/{task}/prepare-email', [TaskController::class, 'showEmailPreparationForm'])->name('tasks.prepare-email');
        Route::post('tasks/{task}/prepare-email', [TaskController::class, 'storeEmailPreparation'])->name('tasks.store-email-preparation');
        Route::post('tasks/{task}/send-confirmation-email', [TaskController::class, 'sendConfirmationEmail'])->name('tasks.send-confirmation-email');
        Route::get('gmail-status', [TaskController::class, 'getGmailStatus'])->name('gmail.status');

        // Test Gmail connection
        Route::get('test-gmail-config', function () {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Not authenticated']);
            }

            $gmailService = app(\App\Services\GmailOAuthService::class);

            return response()->json([
                'user_id' => $user->id,
                'user_email' => $user->email,
                'gmail_client_id' => substr(config('services.gmail.client_id'), 0, 20) . '...',
                'gmail_redirect_uri' => config('services.gmail.redirect_uri'),
                'gmail_auth_url' => $gmailService->getAuthUrl(),
                'app_env' => config('app.env'),
                'app_debug' => config('app.debug')
            ]);
        })->name('test-gmail-config');

        Route::get('test-gmail-connection', function () {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Not authenticated']);
            }

            $gmailService = app(\App\Services\GmailOAuthService::class);

            return response()->json([
                'user_id' => $user->id,
                'user_email' => $user->email,
                'gmail_connected' => $user->gmail_connected,
                'gmail_connected_at' => $user->gmail_connected_at,
                'has_gmail_token' => !empty($user->gmail_token),
                'has_refresh_token' => !empty($user->gmail_refresh_token),
                'has_access_token' => !empty($user->gmail_access_token),
                'gmail_email' => $gmailService->getGmailEmail($user),
                'can_send_emails' => $gmailService->isConnected($user)
            ]);
        })->name('test-gmail-connection');

        Route::get('test-gmail', function () {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Not authenticated']);
            }

            $gmailService = app(\App\Services\GmailOAuthService::class);

            // Check configuration first
            $configCheck = $gmailService->checkConfiguration();

            if (!$configCheck['configured']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gmail API not properly configured',
                    'issues' => $configCheck['issues'],
                    'config' => $configCheck['config']
                ]);
            }

            // Test API connection
            $apiTest = $gmailService->testApiConnection();

            $result = $gmailService->testGmailConnection($user);
            $result['config_check'] = $configCheck;
            $result['api_test'] = $apiTest;

            // Add email comparison
            $result['user_email'] = $user->email;
            $result['gmail_email'] = $gmailService->getGmailEmail($user);
            $result['emails_match'] = $user->email === $result['gmail_email'];

            return response()->json($result);
        })->name('test-gmail');

        // Test task status
        Route::get('test-task-status/{task}', function (\App\Models\Task $task) {
            return response()->json([
                'task_id' => $task->id,
                'current_status' => $task->status,
                'status_type' => gettype($task->status),
                'status_length' => strlen($task->status ?? ''),
                'status_trimmed' => trim($task->status ?? ''),
                'status_equals' => $task->status === 'submitted_for_review',
                'status_in_array' => in_array($task->status, ['submitted_for_review']),
                'all_statuses' => \DB::select("SHOW COLUMNS FROM tasks LIKE 'status'")[0]->Type ?? 'unknown',
            ]);
        })->name('test-task-status');
    });

    // Task destroy - Manager only
    Route::middleware(['manager', 'task.access'])->group(function () {
        Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    });

    // Task attachment download - Available to assigned users and managers
    Route::get('tasks/attachments/{attachment}/download', [TaskController::class, 'downloadAttachment'])->name('tasks.attachments.download');

    // Notification routes - Available to all authenticated users
    Route::get('notifications', [TaskController::class, 'notifications'])->name('notifications.index');
    Route::post('notifications/{notification}/read', [TaskController::class, 'markNotificationAsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [TaskController::class, 'markAllNotificationsAsRead'])->name('notifications.read-all');

    // API routes for live notifications - Available to all authenticated users
    Route::get('api/notifications/unread', [TaskController::class, 'getUnreadNotifications'])->name('api.notifications.unread');
    Route::get('api/notifications/count', [TaskController::class, 'getNotificationCount'])->name('api.notifications.count');


    // Test route for notification sound (remove in production)
    Route::post('test-notification', function () {
        \App\Models\UnifiedNotification::createTaskNotification(
            auth()->id(),
            'test',
            'Test Notification',
            'This is a test notification to check the sound functionality.',
            ['test' => true],
            null,
            'normal'
        );
        return response()->json(['success' => true, 'message' => 'Test notification created']);
    })->name('test.notification');

    // Test route for task workflow (remove in production)
    Route::post('test-task-workflow', function () {
        $user = auth()->user();
        $task = \App\Models\Task::where('assigned_to', $user->id)->where('status', 'assigned')->first();

        if (!$task) {
            return response()->json(['error' => 'No assigned task found for testing'], 404);
        }

        try {
            $task->acceptTask();
            return response()->json(['success' => true, 'message' => 'Task accepted and status changed to in_progress', 'task_id' => $task->id]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    })->name('test.task.workflow');

    // Test route for submit for review (remove in production)
    Route::post('test-submit-review', function () {
        $user = auth()->user();
        $task = \App\Models\Task::where('assigned_to', $user->id)->where('status', 'in_progress')->first();

        if (!$task) {
            return response()->json(['error' => 'No in_progress task found for testing'], 404);
        }

        try {
            $task->submitForReview('Test completion notes');
            return response()->json(['success' => true, 'message' => 'Task submitted for review successfully', 'task_id' => $task->id, 'new_status' => $task->fresh()->status]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    })->name('test.submit.review');

    // Test route to check if form submission works
    Route::post('test-form-submission', function (\Illuminate\Http\Request $request) {
        Log::info('Test form submission received', [
            'all_data' => $request->all(),
            'completion_notes' => $request->input('completion_notes'),
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Form submission received',
            'data' => $request->all()
        ]);
    })->name('test.form.submission');
});

// Email webhook routes (no authentication required for webhooks)
Route::post('/webhook/email/incoming', [EmailController::class, 'handleIncomingEmail'])->name('email.webhook.incoming');
Route::post('/webhook/email/test', [App\Http\Controllers\EmailMonitoringController::class, 'testWebhook'])->name('email.webhook.test');

// Email reply webhook routes
Route::post('/webhook/email/reply', [App\Http\Controllers\EmailReplyWebhookController::class, 'handleReply'])->name('email.webhook.reply');
Route::post('/webhook/email/test-reply', [App\Http\Controllers\EmailReplyWebhookController::class, 'testReply'])->name('email.webhook.test-reply');
Route::get('/webhook/email/recent-emails', [App\Http\Controllers\EmailReplyWebhookController::class, 'getRecentEmails'])->name('email.webhook.recent-emails');

// Email reply testing routes (for debugging)
Route::post('/test/email/simulate-reply', [App\Http\Controllers\EmailReplyTestController::class, 'simulateReply'])->name('test.email.simulate-reply');
Route::get('/test/email/recent-emails', [App\Http\Controllers\EmailReplyTestController::class, 'getRecentEmails'])->name('test.email.recent-emails');
Route::post('/test/email/check-replies', [App\Http\Controllers\EmailReplyTestController::class, 'checkAllReplies'])->name('test.email.check-replies');
Route::get('/test/email/notification-stats', [App\Http\Controllers\EmailReplyTestController::class, 'getNotificationStats'])->name('test.email.notification-stats');
Route::get('/test/email/debug', [App\Http\Controllers\EmailDebugController::class, 'debug'])->name('test.email.debug');

// Designers inbox monitoring routes
Route::post('/webhook/designers-inbox', [App\Http\Controllers\DesignersInboxWebhookController::class, 'handleIncomingEmail'])->name('webhook.designers-inbox');
Route::post('/webhook/designers-inbox/test', [App\Http\Controllers\DesignersInboxWebhookController::class, 'testWebhook'])->name('webhook.designers-inbox.test');
Route::get('/test/designers-inbox/imap', [App\Http\Controllers\DesignersInboxWebhookController::class, 'testImapConnection'])->name('test.designers-inbox.imap');

// Live email testing routes (for immediate testing)
Route::post('/live/test-reply', [App\Http\Controllers\LiveEmailTestController::class, 'createTestReply'])->name('live.test-reply');
Route::get('/live/notification-status', [App\Http\Controllers\LiveEmailTestController::class, 'getNotificationStatus'])->name('live.notification-status');
Route::post('/live/simulate-designers-reply', [App\Http\Controllers\LiveEmailTestController::class, 'simulateDesignersReply'])->name('live.simulate-designers-reply');

// Quick test route
Route::get('/quick-test', [App\Http\Controllers\QuickTestController::class, 'quickTest'])->name('quick-test');

// Simple notification test routes
Route::get('/create-notification', [App\Http\Controllers\SimpleNotificationTestController::class, 'createNotification'])->name('create-notification');
Route::get('/check-notifications', [App\Http\Controllers\SimpleNotificationTestController::class, 'checkNotifications'])->name('check-notifications');

// Debug notification routes
Route::get('/debug-notifications', function () {
    return view('emails.debug-notifications');
})->name('debug-notifications');
Route::post('/create-notification-for-user', [App\Http\Controllers\DebugNotificationController::class, 'createNotificationForUser'])->name('create-notification-for-user');

// Email management routes (authenticated)
Route::middleware('auth')->group(function () {
    Route::get('/emails', [EmailController::class, 'index'])->name('emails.index');
    Route::post('/emails/check-new', [EmailController::class, 'checkNewEmails'])->name('emails.check-new');

    // Email fetching routes
    Route::get('/emails-all', [App\Http\Controllers\EmailFetchController::class, 'index'])->name('emails.all');
    Route::post('/emails/fetch-store', [App\Http\Controllers\EmailFetchController::class, 'fetchAndStore'])->name('emails.fetch-store');
    Route::post('/emails/search', [App\Http\Controllers\EmailFetchController::class, 'search'])->name('emails.search');
    Route::get('/emails/stats', [App\Http\Controllers\EmailFetchController::class, 'getStats'])->name('emails.stats');
    Route::get('/emails/export', [App\Http\Controllers\EmailFetchController::class, 'export'])->name('emails.export');
    Route::get('/emails/{id}', [App\Http\Controllers\EmailFetchController::class, 'show'])->name('emails.show');
    Route::get('/email/{id}', [App\Http\Controllers\EmailController::class, 'show'])->name('email.show');
    Route::post('/emails/{id}/mark-read', [App\Http\Controllers\EmailFetchController::class, 'markAsRead'])->name('emails.mark-read');
    Route::post('/emails/{id}/mark-unread', [App\Http\Controllers\EmailFetchController::class, 'markAsUnread'])->name('emails.mark-unread');
    Route::delete('/emails/{id}', [App\Http\Controllers\EmailFetchController::class, 'destroy'])->name('emails.destroy');
    Route::post('/emails/bulk-action', [App\Http\Controllers\EmailFetchController::class, 'bulkAction'])->name('emails.bulk-action');

    // Debug route for email parsing
    Route::get('/emails/{id}/debug', [App\Http\Controllers\EmailFetchController::class, 'debugEmail'])->name('emails.debug');

    // Auto email fetching routes
    Route::post('/auto-emails/fetch', [App\Http\Controllers\AutoEmailController::class, 'autoFetch'])->name('auto-emails.fetch');
    Route::get('/auto-emails/unread-count', [App\Http\Controllers\AutoEmailController::class, 'getUnreadCount'])->name('auto-emails.unread-count');
    Route::get('/auto-emails/recent-notifications', [App\Http\Controllers\AutoEmailController::class, 'getRecentNotifications'])->name('auto-emails.recent-notifications');
    Route::post('/auto-emails/notifications/{id}/mark-read', [App\Http\Controllers\AutoEmailController::class, 'markAsRead'])->name('auto-emails.mark-read');
    Route::post('/auto-emails/notifications/mark-all-read', [App\Http\Controllers\AutoEmailController::class, 'markAllAsRead'])->name('auto-emails.mark-all-read');
    Route::get('/auto-emails/statistics', [App\Http\Controllers\AutoEmailController::class, 'getStatistics'])->name('auto-emails.statistics');
});

// Notification routes
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/tasks', [App\Http\Controllers\NotificationController::class, 'taskNotifications'])->name('notifications.tasks');
    Route::get('/notifications/emails', [App\Http\Controllers\NotificationController::class, 'emailNotifications'])->name('notifications.emails');
    Route::get('/notifications/stats', [App\Http\Controllers\NotificationController::class, 'stats'])->name('notifications.stats');
    Route::get('/notifications/unread-count', [App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{id}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/{id}/archive', [App\Http\Controllers\NotificationController::class, 'archive'])->name('notifications.archive');
    Route::delete('/notifications/{id}', [App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
});

require __DIR__ . '/auth.php';
