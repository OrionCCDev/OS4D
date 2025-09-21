<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
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
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

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
});

require __DIR__.'/auth.php';
