<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFolderController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ContractorController;
use App\Http\Controllers\ProjectManagerController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExternalStakeholderController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\GmailOAuthController;
use App\Http\Controllers\MailboxWebhookController;
use App\Http\Controllers\EmailNotificationController;
use App\Services\EmailTrackingService;

// Mailbox webhook routes (no auth required for webhooks)
Route::post('/mailbox/webhook', [MailboxWebhookController::class, 'handle']);

// Email fetch webhook routes (no auth required for webhooks)
Route::post('/email/fetch', [App\Http\Controllers\EmailWebhookController::class, 'triggerEmailFetch'])
    ->withoutMiddleware(['web']); // Exclude CSRF protection for webhooks
Route::get('/email/status', [App\Http\Controllers\EmailWebhookController::class, 'status']);

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

// Evaluation routes
Route::get('/evaluations', [App\Http\Controllers\EvaluationController::class, 'index'])->middleware(['auth', 'verified'])->name('evaluations.index');
Route::get('/evaluations/{user}', [App\Http\Controllers\EvaluationController::class, 'show'])->middleware(['auth', 'verified'])->name('evaluations.show');
Route::get('/evaluations/{user}/report', [App\Http\Controllers\EvaluationController::class, 'report'])->middleware(['auth', 'manager'])->name('evaluations.report');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/notification-preferences', [ProfileController::class, 'updateNotificationPreferences'])->name('profile.notification-preferences.update');
    Route::patch('/profile/image', [ProfileController::class, 'updateImage'])->name('profile.image.update');
    Route::delete('/profile/image', [ProfileController::class, 'removeImage'])->name('profile.image.remove');
    Route::get('/profile/signature-preview', [ProfileController::class, 'getSignaturePreview'])->name('profile.signature-preview');
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

    // Reports routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [App\Http\Controllers\ReportController::class, 'index'])->name('index');

        // Project reports
        Route::get('/projects', [App\Http\Controllers\ReportController::class, 'projects'])->name('projects');
        Route::get('/projects/progress', [App\Http\Controllers\ReportController::class, 'projectProgress'])->name('projects.progress');
        Route::get('/projects/{project}/summary', [App\Http\Controllers\ReportController::class, 'projectSummary'])->name('projects.summary');
        Route::get('/projects/{project}/summary/pdf', [App\Http\Controllers\ReportController::class, 'projectSummaryPdf'])->name('projects.summary.pdf');
        Route::get('/projects/{project}/full-report', [App\Http\Controllers\ReportController::class, 'exportFullProjectReport'])->name('projects.full-report');

        // Task reports
        Route::get('/tasks', [App\Http\Controllers\ReportController::class, 'tasks'])->name('tasks');

        // User reports
        Route::get('/users', [App\Http\Controllers\ReportController::class, 'users'])->name('users');
        Route::get('/users/{user}', [App\Http\Controllers\ReportController::class, 'userPerformance'])->name('users.performance');

        // Evaluation reports
        Route::get('/evaluations', [App\Http\Controllers\ReportController::class, 'evaluations'])->name('evaluations');
        Route::post('/evaluations/monthly', [App\Http\Controllers\ReportController::class, 'generateMonthlyEvaluation'])->name('evaluations.monthly');
        Route::post('/evaluations/quarterly', [App\Http\Controllers\ReportController::class, 'generateQuarterlyEvaluation'])->name('evaluations.quarterly');
        Route::post('/evaluations/annual', [App\Http\Controllers\ReportController::class, 'generateAnnualEvaluation'])->name('evaluations.annual');
        Route::post('/evaluations/rankings', [App\Http\Controllers\ReportController::class, 'calculateRankings'])->name('evaluations.rankings');
        Route::post('/evaluations/bulk-pdf', [App\Http\Controllers\ReportController::class, 'generateBulkEvaluationPdf'])->name('evaluations.bulk.pdf');
        Route::post('/evaluations/test-monthly-report', [App\Http\Controllers\ReportController::class, 'sendTestMonthlyReport'])->name('evaluations.test.monthly');

        // Export routes
        Route::get('/export/pdf/{type}', [App\Http\Controllers\ReportController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/export/excel/{type}', [App\Http\Controllers\ReportController::class, 'exportExcel'])->name('export.excel');

        // API routes for filters
        Route::get('/api/users', [App\Http\Controllers\ReportController::class, 'getUsers'])->name('api.users');
        Route::get('/api/projects', [App\Http\Controllers\ReportController::class, 'getProjects'])->name('api.projects');
        Route::get('/api/users/{user}/metrics', [App\Http\Controllers\ReportController::class, 'getUserMetrics'])->name('api.user.metrics');

        // Debug route for search testing
        Route::get('/debug-search', function(Request $request) {
            $searchTerm = $request->get('search');
            $projects = \App\Models\Project::where('name', 'like', "%{$searchTerm}%")
                ->orWhere('short_code', 'like', "%{$searchTerm}%")
                ->get(['id', 'name', 'short_code']);
            return response()->json([
                'search_term' => $searchTerm,
                'projects' => $projects,
                'count' => $projects->count()
            ]);
        })->name('debug.search');
    });

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

        // Queue Monitor Routes (managers only)
        Route::get('/queue-monitor', [App\Http\Controllers\QueueMonitorController::class, 'index'])->name('queue.monitor');
        Route::get('/queue-monitor/stats', [App\Http\Controllers\QueueMonitorController::class, 'getStats'])->name('queue.stats');
        Route::post('/queue-monitor/retry/{jobId}', [App\Http\Controllers\QueueMonitorController::class, 'retryJob'])->name('queue.retry');
        Route::post('/queue-monitor/retry-all', [App\Http\Controllers\QueueMonitorController::class, 'retryAllJobs'])->name('queue.retry-all');
        Route::post('/queue-monitor/delete/{jobId}', [App\Http\Controllers\QueueMonitorController::class, 'deleteJob'])->name('queue.delete');
        Route::post('/queue-monitor/flush', [App\Http\Controllers\QueueMonitorController::class, 'flushFailedJobs'])->name('queue.flush');
        Route::post('/queue-monitor/reset-stuck-emails', [App\Http\Controllers\QueueMonitorController::class, 'resetStuckEmails'])->name('queue.reset-stuck-emails');
        Route::post('/queue-monitor/retry-email/{emailId}', [App\Http\Controllers\QueueMonitorController::class, 'retryEmail'])->name('queue.retry-email');
    });

    // Project workflow resources - Manager onlyst
    Route::middleware('manager')->group(function () {
        Route::resource('projects', ProjectController::class);
        Route::resource('folders', ProjectFolderController::class)->parameters(['folders' => 'folder'])->except(['show']);
        Route::resource('contractors', ContractorController::class)->except(['show']);
        Route::resource('project-managers', ProjectManagerController::class);
        Route::resource('email-templates', EmailTemplateController::class)->parameters(['email-templates' => 'email_template'])->except(['show']);
        Route::resource('external-stakeholders', ExternalStakeholderController::class);

        // Project folder files management
        Route::get('projects/{project}/files', [App\Http\Controllers\ProjectFolderFileController::class, 'index'])->name('projects.files.index');
        Route::post('projects/{project}/files', [App\Http\Controllers\ProjectFolderFileController::class, 'store'])->name('projects.files.store');
        Route::put('projects/{project}/files/{file}', [App\Http\Controllers\ProjectFolderFileController::class, 'update'])->name('projects.files.update');
        Route::delete('projects/{project}/files/{file}', [App\Http\Controllers\ProjectFolderFileController::class, 'destroy'])->name('projects.files.destroy');
        Route::get('projects/{project}/files/{file}/download', [App\Http\Controllers\ProjectFolderFileController::class, 'download'])->name('projects.files.download');

        // Explicitly bind the file parameter to ProjectFolderFile model
        Route::bind('file', function ($value) {
            return \App\Models\ProjectFolderFile::findOrFail($value);
        });
    });

    // User routes for viewing projects (read-only)
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('projects', [ProjectController::class, 'userIndex'])->name('projects.index');
        Route::get('projects/{project}', [ProjectController::class, 'userShow'])->name('projects.show');
        Route::get('projects/{project}/files', [App\Http\Controllers\ProjectFolderFileController::class, 'index'])->name('projects.files.index');
        Route::get('projects/{project}/files/{file}/download', [App\Http\Controllers\ProjectFolderFileController::class, 'download'])->name('projects.files.download');

        // Explicitly bind the file parameter to ProjectFolderFile model
        Route::bind('file', function ($value) {
            return \App\Models\ProjectFolderFile::findOrFail($value);
        });
    });

    // Tasks - Restricted access for non-managers
    Route::middleware('task.access')->group(function () {
        Route::resource('tasks', TaskController::class)->except(['destroy']);
        Route::post('tasks/{task}/assign', [TaskController::class, 'assign'])->name('tasks.assign');
        Route::post('tasks/{task}/change-status', [TaskController::class, 'changeStatus'])->name('tasks.change-status');
        Route::post('tasks/{task}/attachments', [TaskController::class, 'uploadAttachment'])->name('tasks.attachments.upload');
        Route::delete('tasks/{task}/attachments/{attachment}', [TaskController::class, 'deleteAttachment'])->name('tasks.attachments.delete');
        Route::put('tasks/{task}/attachments/{attachment}/mark-required', [TaskController::class, 'markAttachmentAsRequired'])->name('tasks.attachments.mark-required');
        Route::put('tasks/{task}/attachments/bulk-mark-required', [TaskController::class, 'bulkMarkAttachmentsAsRequired'])->name('tasks.attachments.bulk-mark-required');
        Route::get('tasks/{task}/required-files', [TaskController::class, 'getRequiredFilesForEmail'])->name('tasks.required-files');

        // Task workflow routes
        Route::post('tasks/{task}/accept', [TaskController::class, 'acceptTask'])->name('tasks.accept');
        Route::post('tasks/{task}/submit-review', [TaskController::class, 'submitForReview'])->name('tasks.submit-review');
        Route::post('tasks/{task}/start-review', [TaskController::class, 'startReview'])->name('tasks.start-review');
        Route::post('tasks/{task}/approve', [TaskController::class, 'approveTask'])->name('tasks.approve');
        Route::post('tasks/{task}/reject', [TaskController::class, 'rejectTask'])->name('tasks.reject');

        // Internal approval routes
        Route::post('tasks/{task}/internal-approval', [TaskController::class, 'updateInternalApproval'])->name('tasks.internal-approval');

        // Client/Consultant response tracking routes
        Route::post('tasks/{task}/client-response', [TaskController::class, 'updateClientResponse'])->name('tasks.client-response');
        Route::post('tasks/{task}/consultant-response', [TaskController::class, 'updateConsultantResponse'])->name('tasks.consultant-response');
        Route::post('tasks/{task}/finish-review', [TaskController::class, 'finishReview'])->name('tasks.finish-review');

        // Manager actions after client/consultant review
        Route::post('tasks/{task}/mark-completed', [TaskController::class, 'markAsCompleted'])->name('tasks.mark-completed');
        Route::post('tasks/{task}/require-resubmit', [TaskController::class, 'requireResubmit'])->name('tasks.require-resubmit');

        // User resubmit action
        Route::post('tasks/{task}/resubmit', [TaskController::class, 'resubmitTask'])->name('tasks.resubmit');

        // Manager override routes
        Route::post('tasks/{task}/manager-override', [TaskController::class, 'managerOverride'])->name('tasks.manager-override');
        Route::post('tasks/{task}/send-approval-email', [TaskController::class, 'sendApprovalEmail'])->name('tasks.send-approval-email');
        Route::post('tasks/{task}/send-rejection-email', [TaskController::class, 'sendRejectionEmail'])->name('tasks.send-rejection-email');

        // Time extension routes
        Route::post('tasks/{task}/request-time-extension', [TaskController::class, 'requestTimeExtension'])->name('tasks.request-time-extension');
        Route::post('tasks/{task}/review-time-extension', [TaskController::class, 'reviewTimeExtension'])->name('tasks.review-time-extension');

        // Client/Consultant approval routes
        Route::post('tasks/{task}/move-to-waiting-sending-approval', [TaskController::class, 'moveToWaitingSendingApproval'])->name('tasks.move-to-waiting-sending-approval');
        Route::post('tasks/{task}/send-for-client-consultant-approval', [TaskController::class, 'sendForClientConsultantApproval'])->name('tasks.send-for-client-consultant-approval');
        Route::post('tasks/{task}/update-client-approval', [TaskController::class, 'updateClientApproval'])->name('tasks.update-client-approval');
        Route::post('tasks/{task}/update-consultant-approval', [TaskController::class, 'updateConsultantApproval'])->name('tasks.update-consultant-approval');

        // Email preparation routes
        Route::get('tasks/{task}/prepare-email', [TaskController::class, 'showEmailPreparationForm'])->name('tasks.prepare-email');
        Route::post('tasks/{task}/prepare-email', [TaskController::class, 'storeEmailPreparation'])->name('tasks.store-email-preparation');
        Route::post('tasks/{task}/send-confirmation-email', [TaskController::class, 'sendConfirmationEmail'])->name('tasks.send-confirmation-email');
        Route::post('tasks/{task}/mark-email-sent', [TaskController::class, 'markEmailAsSent'])->name('tasks.mark-email-sent');
        Route::get('tasks/{task}/debug-attachments', [TaskController::class, 'debugEmailAttachments'])->name('tasks.debug-attachments');
        Route::get('gmail-status', [TaskController::class, 'getGmailStatus'])->name('gmail.status');

        // Free mail sending feature
        Route::get('tasks/{task}/free-mail', [TaskController::class, 'showFreeMailForm'])->name('tasks.free-mail');
        Route::post('tasks/{task}/free-mail', [TaskController::class, 'sendFreeMail'])->name('tasks.send-free-mail');

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

    // Task history - Available to assigned users and managers
    Route::get('tasks/{task}/history', [TaskController::class, 'getTaskHistory'])->name('tasks.history');

    // Task reassignment routes
    Route::post('tasks/{task}/reassign', [App\Http\Controllers\TaskReassignmentController::class, 'reassignTask'])->name('tasks.reassign');

    // Task bulk reassignment routes - Manager only
    Route::middleware('manager.or.admin')->group(function () {
        Route::get('users/{user}/bulk-reassignment', [App\Http\Controllers\TaskReassignmentController::class, 'showBulkReassignment'])->name('users.bulk-reassignment');
        Route::post('tasks/bulk-reassign', [App\Http\Controllers\TaskReassignmentController::class, 'bulkReassign'])->name('tasks.bulk-reassign');
        Route::post('users/{user}/status', [App\Http\Controllers\TaskReassignmentController::class, 'updateUserStatus'])->name('users.update-status');
        Route::get('users/{user}/active-tasks', [App\Http\Controllers\TaskReassignmentController::class, 'getUserActiveTasks'])->name('users.active-tasks');
    });

    // Debug routes (temporary)
    Route::get('debug/notifications', [App\Http\Controllers\DebugController::class, 'showReassignmentDebug'])->name('debug.notifications');
    Route::get('debug/test-reassignment', [App\Http\Controllers\DebugController::class, 'testReassignment'])->name('debug.test-reassignment');

    // Notification routes - Available to all authenticated users (moved to unified notification system below)

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
Route::post('/webhook/email/incoming', [EmailController::class, 'handleIncomingEmail'])->name('email.webhook.incoming.alternative');
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

    // General email routes
    Route::get('/emails/send', [TaskController::class, 'showGeneralEmailForm'])->name('emails.send-form');
    Route::post('/emails/send', [TaskController::class, 'sendGeneralEmail'])->name('emails.send-general');
    Route::post('/emails/check-new', [EmailController::class, 'checkNewEmails'])->name('emails.check-new');

    // Email fetching routes
    Route::get('/emails-all', [App\Http\Controllers\EmailFetchController::class, 'index'])->name('emails.all');
    Route::post('/emails/fetch-store', [App\Http\Controllers\EmailFetchController::class, 'fetchAndStore'])->name('emails.fetch-store');
    Route::post('/emails/search', [App\Http\Controllers\EmailFetchController::class, 'search'])->name('emails.search');
    Route::get('/emails/stats', [App\Http\Controllers\EmailFetchController::class, 'getStats'])->name('emails.stats');
    Route::get('/emails/export', [App\Http\Controllers\EmailFetchController::class, 'export'])->name('emails.export');
    Route::get('/emails/{id}', [App\Http\Controllers\EmailFetchController::class, 'show'])->name('emails.show');
    Route::get('/emails/{id}/standalone', [App\Http\Controllers\EmailFetchController::class, 'showStandalone'])->name('emails.show-standalone');
    Route::get('/emails/{emailId}/attachment/{attachmentIndex}/preview', [App\Http\Controllers\EmailFetchController::class, 'previewAttachment'])->name('emails.attachment.preview');
    Route::get('/emails/{emailId}/attachment/{attachmentIndex}/download', [App\Http\Controllers\EmailFetchController::class, 'downloadAttachment'])->name('emails.attachment.download');

    // Public attachment download with token (no auth required)
    Route::get('/emails/{emailId}/attachment/{attachmentIndex}/download/{token}', [App\Http\Controllers\EmailFetchController::class, 'downloadAttachmentPublic'])->name('emails.attachment.download.public');
    Route::get('/emails/{emailId}/attachment/{attachmentIndex}/view', [App\Http\Controllers\EmailFetchController::class, 'viewAttachment'])->name('emails.attachment.view');
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
