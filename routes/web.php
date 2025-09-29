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
    Route::get('/auth/gmail/status', [GmailOAuthController::class, 'status'])->name('gmail.status');

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
            public function array(): array { return $this->data; }
        }, 'report.xlsx');
    })->name('reports.excel');

    Route::get('/reports/pdf', function () {
        $html = view('reports.sample', ['title' => 'Sample Report'])->render();
        $pdf = Pdf::loadHTML($html);
        return $pdf->download('report.pdf');
    })->name('reports.pdf');

    // Admin: Users CRUD
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UsersController::class)->except(['show']);
    });

    // Project workflow resources - Manager only
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
        Route::get('gmail-status', [TaskController::class, 'getGmailStatus'])->name('tasks.gmail-status');

        // Test Gmail connection
        Route::get('test-gmail', function() {
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

            return response()->json($result);
        })->name('test-gmail');
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
    Route::post('test-notification', function() {
        \App\Models\CustomNotification::create([
            'user_id' => auth()->id(),
            'type' => 'test',
            'title' => 'Test Notification',
            'message' => 'This is a test notification to check the sound functionality.',
            'data' => ['test' => true]
        ]);
        return response()->json(['success' => true, 'message' => 'Test notification created']);
    })->name('test.notification');

    // Test route for task workflow (remove in production)
    Route::post('test-task-workflow', function() {
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
    Route::post('test-submit-review', function() {
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
    Route::post('test-form-submission', function(\Illuminate\Http\Request $request) {
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

// Email management routes (authenticated)
Route::middleware('auth')->group(function () {
    Route::get('/emails', [EmailController::class, 'index'])->name('emails.index');
    Route::get('/emails/{email}', [EmailController::class, 'show'])->name('emails.show');
    Route::post('/emails/{email}/mark-read', [EmailController::class, 'markAsRead'])->name('emails.mark-read');
    Route::post('/emails/check-new', [EmailController::class, 'checkNewEmails'])->name('emails.check-new');
});

require __DIR__.'/auth.php';
