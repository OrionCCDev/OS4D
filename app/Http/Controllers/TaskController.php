<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectFolder;
use App\Models\Task;
use App\Models\User;
use App\Models\Contractor;
use App\Models\TaskHistory;
use App\Models\CustomNotification;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendTaskConfirmationEmailJob;

class TaskController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Managers and admins see all tasks
        if ($user->isManager()) {
            $tasks = Task::with('project', 'folder', 'creator', 'assignee')->latest()->paginate(15);
        } else {
            // Regular users only see tasks assigned to them
            $tasks = Task::with('project', 'folder', 'creator', 'assignee')
                ->where('assigned_to', $user->id)
                ->latest()
                ->paginate(15);
        }

        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        // Only managers can create tasks
        if (!Auth::user()->isManager()) {
            abort(403, 'Access denied. Only managers can create tasks.');
        }

        $projects = Project::orderBy('name')->get();
        $selectedProjectId = request()->query('project_id');
        $folders = $selectedProjectId
            ? ProjectFolder::where('project_id', $selectedProjectId)->orderBy('name')->get()
            : ProjectFolder::orderBy('name')->get();
        $users = User::where('id', '!=', Auth::id())
            ->where('role', 'user')
            ->orderBy('name')
            ->get();

        $contractors = Contractor::orderBy('name')->get();

        // Preselect context if provided in query
        $selectedFolderId = request()->query('folder_id');

        // Default due date is one week from today
        $defaultDueDate = now()->addWeek()->format('Y-m-d');

        return view('tasks.create', compact(
            'projects',
            'folders',
            'users',
            'contractors',
            'selectedProjectId',
            'selectedFolderId',
            'defaultDueDate'
        ));
    }

    public function store(Request $request)
    {
        // Only managers can create tasks
        if (!Auth::user()->isManager()) {
            abort(403, 'Access denied. Only managers can create tasks.');
        }

        $rules = [
            'project_id' => 'required|exists:projects,id',
            'folder_id' => 'nullable|exists:project_folders,id',
            'assigned_to' => 'nullable|exists:users,id',
            'contractors' => 'nullable|array',
            'contractors.*' => 'exists:contractors,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:pending,assigned,in_progress,in_review,approved,rejected,completed',
            'priority' => 'nullable|in:low,normal,medium,high,urgent,critical',
        ];

        // Only add file validation if files are actually uploaded
        if ($request->hasFile('attachments')) {
            $rules['attachments.*'] = 'file|max:1024000'; // 1GB max per file
        }

        $validated = $request->validate($rules);

        $validated['created_by'] = Auth::id();
        // Fallback default due date to +1 week if not provided
        if (empty($validated['due_date'])) {
            $validated['due_date'] = now()->addWeek();
        }
        $task = Task::create($validated);

        // Handle attachments on create
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if (!$file) continue;
                $disk = 'public';
                $path = $file->store("tasks/{$task->id}", $disk);
                $att = $task->attachments()->create([
                    'uploaded_by' => Auth::id(),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size_bytes' => $file->getSize(),
                    'disk' => $disk,
                    'path' => $path,
                ]);
                $task->histories()->create([
                    'user_id' => Auth::id(),
                    'action' => 'file_uploaded',
                    'description' => "Uploaded file: {$att->original_name}",
                    'metadata' => ['attachment_id' => $att->id]
                ]);
            }
        }

        // If task is assigned, handle assignment and notify
        if (!empty($task->assigned_to)) {
            if ($assignee = User::find($task->assigned_to)) {
                $task->assignTo($assignee);
            }
        }

        // Handle contractor assignments
        if ($request->has('contractors') && is_array($request->contractors)) {
            // Filter out empty values (from "Not assigned" option)
            $contractorIds = array_filter($request->contractors, function($id) {
                return !empty($id);
            });

            foreach ($contractorIds as $contractorId) {
                if ($contractor = Contractor::find($contractorId)) {
                    $task->addContractor($contractor, 'participant');
                }
            }
        }

        return redirect()->route('tasks.index')->with('success', 'Task created');
    }

    public function edit(Task $task)
    {
        $user = Auth::user();

        // Only managers can edit tasks - regular users can only upload files and change status
        if (!$user->isManager()) {
            abort(403, 'Access denied. Only managers can edit tasks. Regular users can upload files and change status from the task view.');
        }

        // Additional restriction: No editing of tasks under review (but managers can edit completed tasks)
        if (in_array($task->status, ['submitted_for_review', 'in_review', 'approved'])) {
            abort(403, 'Access denied. This task is under review and cannot be edited.');
        }

        $projects = Project::orderBy('name')->get();
        $folders = ProjectFolder::orderBy('name')->get();
        return view('tasks.edit', compact('task', 'projects', 'folders'));
    }

    public function update(Request $request, Task $task)
    {
        $user = Auth::user();

        // Only managers can edit tasks - regular users can only upload files and change status
        if (!$user->isManager()) {
            abort(403, 'Access denied. Only managers can edit tasks. Regular users can upload files and change status from the task view.');
        }

        // Additional restriction: No editing of tasks under review (but managers can edit completed tasks)
        if (in_array($task->status, ['submitted_for_review', 'in_review', 'approved'])) {
            abort(403, 'Access denied. This task is under review and cannot be edited.');
        }

        // Define validation rules for managers
        $rules = [
            'project_id' => 'required|exists:projects,id',
            'folder_id' => 'nullable|exists:project_folders,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:pending,assigned,in_progress,in_review,approved,rejected,completed',
            'priority' => 'nullable|in:low,normal,medium,high,urgent,critical',
        ];

        // Only add file validation if files are actually uploaded
        if ($request->hasFile('attachments')) {
            $rules['attachments.*'] = 'file|max:1024000'; // 1GB max per file
        }

        $validated = $request->validate($rules);

        $task->update($validated);

        // Handle attachments on update (append)
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if (!$file) continue;
                $disk = 'public';
                $path = $file->store("tasks/{$task->id}", $disk);
                $att = $task->attachments()->create([
                    'uploaded_by' => Auth::id(),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size_bytes' => $file->getSize(),
                    'disk' => $disk,
                    'path' => $path,
                ]);
                $task->histories()->create([
                    'user_id' => Auth::id(),
                    'action' => 'file_uploaded',
                    'description' => "Uploaded file: {$att->original_name}",
                    'metadata' => ['attachment_id' => $att->id]
                ]);
            }
        }
        return redirect()->route('tasks.index')->with('success', 'Task updated');
    }

    public function destroy(Task $task)
    {
        // Only admins and managers can delete tasks (sub-admin cannot delete)
        if (!Auth::user()->canDelete()) {
            abort(403, 'Access denied. Only admins and managers can delete tasks.');
        }

        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted');
    }

    // Task assignment methods
    public function assign(Request $request, Task $task)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $user = User::find($validated['assigned_to']);
        $task->assignTo($user);

        return redirect()->back()->with('success', 'Task assigned successfully');
    }

    public function changeStatus(Request $request, Task $task)
    {
        $user = Auth::user();

        // Regular users can only change status for tasks assigned to them and not under review
        if (!$user->isManager()) {
            if ($task->assigned_to !== $user->id) {
                abort(403, 'Access denied. You can only change status of tasks assigned to you.');
            }

            if (in_array($task->status, ['submitted_for_review', 'in_review', 'approved', 'completed'])) {
                abort(403, 'Access denied. Status changes are disabled for tasks under review. Only managers can change the status.');
            }
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,assigned,in_progress,in_review,approved,rejected,completed',
            'notes' => 'nullable|string|max:1000',
        ]);

        $task->changeStatus($validated['status'], $validated['notes'] ?? null);

        return redirect()->back()->with('success', 'Task status updated successfully');
    }

    public function show(Task $task)
    {
        $task->load(['project', 'folder', 'creator', 'assignee', 'histories.user', 'attachments.uploader']);
        return view('tasks.show', compact('task'));
    }

    // Attachments
    public function uploadAttachment(Request $request, Task $task)
    {
        // Restrict file uploads for tasks under review - only managers can upload
        if (in_array($task->status, ['submitted_for_review', 'in_review', 'approved', 'completed']) && !Auth::user()->isManager()) {
            abort(403, 'Access denied. File uploads are disabled for tasks under review. Only managers can upload files.');
        }

        $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|file|max:51200', // 50MB per file
        ]);

        $files = $request->file('files');
        $uploadedCount = 0;
        $uploadedFiles = [];

        foreach ($files as $file) {
            if (!$file) continue;

            $disk = 'public';
            $path = $file->store("tasks/{$task->id}", $disk);

            $attachment = $task->attachments()->create([
                'uploaded_by' => Auth::id(),
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size_bytes' => $file->getSize(),
                'disk' => $disk,
                'path' => $path,
            ]);

            $uploadedFiles[] = $attachment->original_name;
            $uploadedCount++;

            // History record for each file
            $task->histories()->create([
                'user_id' => Auth::id(),
                'action' => 'file_uploaded',
                'description' => "Uploaded file: {$attachment->original_name}",
                'metadata' => ['attachment_id' => $attachment->id]
            ]);
        }

        if ($uploadedCount === 1) {
            return back()->with('success', 'File uploaded successfully');
        } else {
            return back()->with('success', "{$uploadedCount} files uploaded successfully");
        }
    }

    public function deleteAttachment(Task $task, TaskAttachment $attachment)
    {
        abort_unless($attachment->task_id === $task->id, 403);

        // Restrict file deletion for tasks under review - only admins and managers can delete
        if (in_array($task->status, ['submitted_for_review', 'in_review', 'approved', 'completed']) && !Auth::user()->canDelete()) {
            abort(403, 'Access denied. File deletion is disabled for tasks under review. Only admins and managers can delete files.');
        }

        Storage::disk($attachment->disk)->delete($attachment->path);
        $name = $attachment->original_name;
        $attachment->delete();

        $task->histories()->create([
            'user_id' => Auth::id(),
            'action' => 'file_deleted',
            'description' => "Deleted file: {$name}",
        ]);

        return back()->with('success', 'Attachment deleted');
    }

    public function downloadAttachment(TaskAttachment $attachment)
    {
        // Check if user has access to this task
        $user = Auth::user();
        if (!$user->isManager() && $attachment->task->assigned_to !== $user->id) {
            abort(403, 'Access denied. You can only download files from tasks assigned to you.');
        }

        // Check if file exists
        if (!Storage::disk($attachment->disk)->exists($attachment->path)) {
            abort(404, 'File not found');
        }

        return response()->download(Storage::disk($attachment->disk)->path($attachment->path), $attachment->original_name);
    }

    // Notification methods
    public function notifications()
    {
        $notifications = Auth::user()->customNotifications()->latest()->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

    public function markNotificationAsRead(CustomNotification $notification)
    {
        if ($notification->user_id === Auth::id()) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    public function markAllNotificationsAsRead()
    {
        \App\Models\UnifiedNotification::markAllAsReadForUser(Auth::id());

        return response()->json(['success' => true]);
    }

    // API endpoints for live notifications
    public function getUnreadNotifications()
    {
        $notifications = \App\Models\UnifiedNotification::forUser(Auth::id())
            ->unread()
            ->active()
            ->latest()
            ->take(10)
            ->get();
        return response()->json($notifications);
    }

    public function getNotificationCount()
    {
        $count = \App\Models\UnifiedNotification::getUnreadCountForUser(Auth::id());
        return response()->json(['count' => $count]);
    }

    // New workflow methods have been moved to the bottom of the file

    public function approveTask(Request $request, Task $task)
    {
        // Only managers can approve tasks
        if (!Auth::user()->isManager()) {
            abort(403, 'Access denied. Only managers can approve tasks.');
        }

        $validated = $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        try {
            Log::info('Attempting to approve task', [
                'task_id' => $task->id,
                'current_status' => $task->status,
                'manager_id' => Auth::id(),
                'manager_name' => Auth::user()->name
            ]);

            $task->approveTask($validated['approval_notes'] ?? null);

            Log::info('Task approved successfully', [
                'task_id' => $task->id,
                'new_status' => $task->fresh()->status
            ]);

            // Always return JSON response for approval endpoint
            return response()->json([
                'success' => true,
                'message' => 'Task approved successfully',
                'task_id' => $task->id,
                'new_status' => $task->fresh()->status
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to approve task', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
                'manager_id' => Auth::id()
            ]);

            // Always return JSON response for approval endpoint
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function sendApprovalEmail(Request $request, Task $task)
    {
        // Only managers can send approval emails
        if (!Auth::user()->isManager()) {
            abort(403, 'Access denied. Only managers can send approval emails.');
        }

        // Only approved tasks can have emails sent
        if ($task->status !== 'approved') {
            abort(403, 'Access denied. Only approved tasks can have emails sent.');
        }

        try {
            // Send email to the assigned user
            $this->sendTaskApprovalEmail($task);
            return response()->json(['success' => true, 'message' => 'Approval email sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send email: ' . $e->getMessage()]);
        }
    }

    public function sendRejectionEmail(Request $request, Task $task)
    {
        // Only managers can send rejection emails
        if (!Auth::user()->isManager()) {
            abort(403, 'Access denied. Only managers can send rejection emails.');
        }

        // Only rejected tasks can have emails sent
        if ($task->status !== 'rejected') {
            abort(403, 'Access denied. Only rejected tasks can have emails sent.');
        }

        try {
            // Send email to the assigned user
            $this->sendTaskRejectionEmail($task);
            return response()->json(['success' => true, 'message' => 'Rejection email sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send email: ' . $e->getMessage()]);
        }
    }

    private function sendTaskApprovalEmail(Task $task)
    {
        if (!$task->assignee || !$task->assignee->email) {
            throw new \Exception('Task has no assigned user or user has no email address');
        }

        try {
            $approver = Auth::user();

            // FORCE CURRENT USER TO HAVE GMAIL CONNECTED - No fallback to other users
            if (!$approver || !$approver->hasGmailConnected()) {
                Log::error('Gmail OAuth required for approval email but current approver ' . $approver->id . ' (' . $approver->email . ') does not have Gmail connected');
                throw new \Exception('You must connect your own Gmail account to send approval emails. Please go to your profile and connect Gmail first.');
            }

            $this->sendApprovalEmailViaGmail($task, $approver);
            Log::info('Approval email sent successfully for task: ' . $task->id . ' to user: ' . $task->assignee->email . ' via Gmail OAuth only');
        } catch (\Exception $e) {
            Log::error('Failed to send approval email for task: ' . $task->id . ' - ' . $e->getMessage());
            throw $e;
        }
    }

    private function sendTaskRejectionEmail(Task $task)
    {
        if (!$task->assignee || !$task->assignee->email) {
            throw new \Exception('Task has no assigned user or user has no email address');
        }

        try {
            $approver = Auth::user();

            // FORCE CURRENT USER TO HAVE GMAIL CONNECTED - No fallback to other users
            if (!$approver || !$approver->hasGmailConnected()) {
                Log::error('Gmail OAuth required for rejection email but current approver ' . $approver->id . ' (' . $approver->email . ') does not have Gmail connected');
                throw new \Exception('You must connect your own Gmail account to send rejection emails. Please go to your profile and connect Gmail first.');
            }

            $this->sendApprovalEmailViaGmail($task, $approver);
            Log::info('Rejection email sent successfully for task: ' . $task->id . ' to user: ' . $task->assignee->email . ' via Gmail OAuth only');
        } catch (\Exception $e) {
            Log::error('Failed to send rejection email for task: ' . $task->id . ' - ' . $e->getMessage());
            throw $e;
        }
    }

    public function rejectTask(Request $request, Task $task)
    {
        // Only managers can reject tasks
        if (!Auth::user()->isManager()) {
            abort(403, 'Access denied. Only managers can reject tasks.');
        }

        $validated = $request->validate([
            'rejection_notes' => 'required|string|max:1000',
        ]);

        try {
            Log::info('Attempting to reject task', [
                'task_id' => $task->id,
                'current_status' => $task->status,
                'manager_id' => Auth::id(),
                'manager_name' => Auth::user()->name
            ]);

            $task->rejectTask($validated['rejection_notes']);

            Log::info('Task rejected successfully', [
                'task_id' => $task->id,
                'new_status' => $task->fresh()->status
            ]);

            // Always return JSON response for rejection endpoint
            return response()->json([
                'success' => true,
                'message' => 'Task rejected successfully',
                'task_id' => $task->id,
                'new_status' => $task->fresh()->status
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to reject task', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
                'manager_id' => Auth::id()
            ]);

            // Always return JSON response for rejection endpoint
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function showEmailPreparationForm(Task $task)
    {
        // Allow assigned user or managers to prepare emails
        if ($task->assigned_to !== Auth::id() && !Auth::user()->isManager()) {
            abort(403, 'Access denied. Only the assigned user or managers can prepare emails for this task.');
        }

        // Only tasks in ready_for_email or approved status can have emails prepared
        if (!in_array($task->status, ['ready_for_email', 'approved'])) {
            abort(403, 'Access denied. Only tasks ready for email can have emails prepared.');
        }

        // Look for any email preparation for this task, prioritizing current user's preparation
        $emailPreparation = $task->emailPreparations()->where('prepared_by', Auth::id())->latest()->first();

        // If no preparation by current user, get the latest one for the task
        if (!$emailPreparation) {
            $emailPreparation = $task->emailPreparations()->latest()->first();
        }

        return view('tasks.email-preparation', compact('task', 'emailPreparation'));
    }

    public function storeEmailPreparation(Request $request, Task $task)
    {
        // Allow assigned user or managers to prepare emails
        if ($task->assigned_to !== Auth::id() && !Auth::user()->isManager()) {
            abort(403, 'Access denied. Only the assigned user or managers can prepare emails for this task.');
        }

        // Only tasks in ready_for_email or approved status can have emails prepared
        if (!in_array($task->status, ['ready_for_email', 'approved'])) {
            abort(403, 'Access denied. Only tasks ready for email can have emails prepared.');
        }

        $validated = $request->validate([
            'to_emails' => 'required|string',
            'cc_emails' => 'nullable|string',
            'bcc_emails' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:102400', // 100MB max per file
        ]);

        try {
            // Handle file uploads
            $attachmentPaths = [];
            if ($request->hasFile('attachments')) {
                Log::info('Processing file uploads - Count: ' . count($request->file('attachments')));
                foreach ($request->file('attachments') as $file) {
                    if (!$file) continue; // Skip empty files

                    $originalName = $file->getClientOriginalName();
                    $fileSize = $file->getSize();
                    Log::info('Uploading file: ' . $originalName . ' - Size: ' . $fileSize . ' bytes');

                    // Store file with a unique name to avoid conflicts
                    // Use email_attachments disk which has root at storage/app
                    $path = $file->store('email-attachments', 'email_attachments');
                    $attachmentPaths[] = $path;
                    Log::info('File stored at: ' . $path);
                }
            } else {
                Log::info('No files uploaded in request');
            }

            // Create or update email preparation
            $emailPreparation = $task->emailPreparations()->updateOrCreate(
                ['prepared_by' => Auth::id()],
                [
                    'to_emails' => $validated['to_emails'],
                    'cc_emails' => $validated['cc_emails'],
                    'bcc_emails' => $validated['bcc_emails'],
                    'subject' => $validated['subject'],
                    'body' => $validated['body'],
                    'attachments' => $attachmentPaths,
                    'status' => 'draft',
                ]
            );

            Log::info('Email preparation saved with attachments: ' . json_encode($attachmentPaths));

            // Check if this is an AJAX request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email preparation saved successfully!',
                    'redirect_url' => route('tasks.show', $task)
                ]);
            }

            return redirect()->back()->with('success', 'Email preparation saved successfully!');
        } catch (\Exception $e) {
            // Check if this is an AJAX request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save email preparation: ' . $e->getMessage()
                ]);
            }

            return redirect()->back()->with('error', 'Failed to save email preparation: ' . $e->getMessage());
        }
    }

    public function sendConfirmationEmail(Request $request, Task $task)
    {
        // Allow assigned user or managers to send emails
        if ($task->assigned_to !== Auth::id() && !Auth::user()->isManager()) {
            abort(403, 'Access denied. Only the assigned user or managers can send emails for this task.');
        }

        // Only tasks in ready_for_email or approved status can have emails sent
        if (!in_array($task->status, ['ready_for_email', 'approved'])) {
            abort(403, 'Access denied. Only tasks ready for email can have emails sent.');
        }

        // If form data is provided, save it first
        if ($request->has('to_emails')) {
            $validated = $request->validate([
                'to_emails' => 'required|string',
                'cc_emails' => 'nullable|string',
                'bcc_emails' => 'nullable|string',
                'subject' => 'required|string|max:255',
                'body' => 'required|string',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|max:102400', // 100MB max per file
            ]);

            // Handle file uploads
            $attachmentPaths = [];
            if ($request->hasFile('attachments')) {
                Log::info('Processing file uploads in sendConfirmationEmail - Count: ' . count($request->file('attachments')));
                foreach ($request->file('attachments') as $file) {
                    if (!$file) continue; // Skip empty files

                    $originalName = $file->getClientOriginalName();
                    $fileSize = $file->getSize();
                    Log::info('Uploading file: ' . $originalName . ' - Size: ' . $fileSize . ' bytes');

                    // Store file with a unique name to avoid conflicts
                    // Use email_attachments disk which has root at storage/app
                    $path = $file->store('email-attachments', 'email_attachments');
                    $attachmentPaths[] = $path;
                    Log::info('File stored at: ' . $path);
                }
            } else {
                Log::info('No files uploaded in sendConfirmationEmail request');
            }

            // Create or update email preparation with form data
            $emailPreparation = $task->emailPreparations()->updateOrCreate(
                ['prepared_by' => Auth::id()],
                [
                    'to_emails' => $validated['to_emails'],
                    'cc_emails' => $validated['cc_emails'],
                    'bcc_emails' => $validated['bcc_emails'],
                    'subject' => $validated['subject'],
                    'body' => $validated['body'],
                    'attachments' => $attachmentPaths,
                    'status' => 'draft',
                ]
            );
        } else {
            // Look for existing email preparation
            $emailPreparation = $task->emailPreparations()->where('status', 'draft')->orderBy('id', 'desc')->first();

            if (!$emailPreparation) {
                // Auto-create a default email preparation if none exists
                $emailPreparation = $this->createDefaultEmailPreparation($task);
            }
        }

        try {
            $user = Auth::user();

            Log::info('Email sending attempt - Current User: ' . $user->id . ' (' . $user->email . ')');
            Log::info('Email preparation - To: ' . $emailPreparation->to_emails . ', CC: ' . ($emailPreparation->cc_emails ?? 'none') . ', BCC: ' . ($emailPreparation->bcc_emails ?? 'none') . ', Subject: ' . $emailPreparation->subject);

            // Dispatch the email sending job to run in the background
            SendTaskConfirmationEmailJob::dispatch($task, $user, $emailPreparation);

            // Update email preparation status to processing
            $emailPreparation->update([
                'status' => 'processing',
            ]);

            // Update task status to on_client_consultant_review
            $task->update(['status' => 'on_client_consultant_review']);

            Log::info('Email sending job dispatched for task: ' . $task->id . ' by user: ' . $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Email is being sent in the background. You will receive a notification when it\'s completed.',
                'redirect_url' => route('tasks.show', $task->id)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to dispatch email sending job for task: ' . $task->id . ' - ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
                'redirect_url' => route('tasks.show', $task->id)
            ]);
        }
    }

    /**
     * Mark email as sent (for manual Gmail sending)
     */
    public function markEmailAsSent(Request $request, Task $task)
    {
        // Allow assigned user or managers to mark emails as sent
        if ($task->assigned_to !== Auth::id() && !Auth::user()->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only the assigned user or managers can mark emails as sent.'
            ], 403);
        }

        try {
            // Get the latest email preparation
            $emailPreparation = $task->emailPreparations()
                ->whereIn('status', ['draft', 'processing'])
                ->orderBy('id', 'desc')
                ->first();

            if (!$emailPreparation) {
                return response()->json([
                    'success' => false,
                    'message' => 'No email preparation found. Please save a draft first.'
                ]);
            }

            $user = Auth::user();
            $sentVia = $request->input('sent_via', 'gmail_manual');

            // Update email preparation status
            $emailPreparation->update([
                'status' => 'sent',
                'sent_at' => now(),
                'sent_via' => $sentVia,
            ]);

            // Update task status to on_client_consultant_review
            $task->update(['status' => 'on_client_consultant_review']);

            // Add task history entry for email sent
            $this->addEmailSentHistory($task, $emailPreparation, $user, $sentVia);

            // Add task history entry for status change to waiting for review
            $this->addWaitingForReviewHistory($task, $user);

            // Send in-app notifications to managers
            $this->sendInAppNotificationsToManagers($task, $emailPreparation, $user);

            Log::info('Email marked as sent manually for task: ' . $task->id . ' by user: ' . Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Email marked as sent successfully! Task status updated to "On Client/Consultant Review".',
                'redirect_url' => route('tasks.show', $task->id)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to mark email as sent for task: ' . $task->id . ' - ' . $e->getMessage());
            Log::error('Exception trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark email as sent: ' . $e->getMessage(),
                'debug' => [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'task_id' => $task->id,
                    'user_id' => Auth::id()
                ]
            ], 500);
        }
    }

    /**
     * Add task history entry when email is marked as sent
     */
    private function addEmailSentHistory(Task $task, $emailPreparation, $user, $sentVia)
    {
        try {
            $toEmails = array_filter(array_map('trim', explode(',', $emailPreparation->to_emails)));
            $ccEmails = $emailPreparation->cc_emails ? array_filter(array_map('trim', explode(',', $emailPreparation->cc_emails))) : [];
            $bccEmails = $emailPreparation->bcc_emails ? array_filter(array_map('trim', explode(',', $emailPreparation->bcc_emails))) : [];

            $task->histories()->create([
                'user_id' => $user->id,
                'action' => 'email_marked_sent',
                'description' => "Email marked as sent by {$user->name} via {$sentVia}",
                'metadata' => [
                    'email_subject' => $emailPreparation->subject,
                    'email_to' => $toEmails,
                    'email_cc' => $ccEmails,
                    'email_bcc' => $bccEmails,
                    'sent_via' => $sentVia,
                    'sent_at' => now()->toISOString(),
                    'has_attachments' => !empty($emailPreparation->attachments),
                    'attachment_count' => is_array($emailPreparation->attachments) ? count($emailPreparation->attachments) : 0,
                    'email_preparation_id' => $emailPreparation->id
                ]
            ]);

            Log::info('Task history entry created for email marked as sent - Task: ' . $task->id);
        } catch (\Exception $e) {
            Log::error('Failed to create task history for email marked as sent: ' . $e->getMessage());
        }
    }

    /**
     * Add task history entry for status change to waiting for review
     */
    private function addWaitingForReviewHistory(Task $task, $user)
    {
        try {
            $task->histories()->create([
                'user_id' => $user->id,
                'action' => 'status_changed',
                'description' => "Task status changed to 'On Client/Consultant Review' - waiting for client and consultant responses",
                'old_value' => 'ready_for_email',
                'new_value' => 'on_client_consultant_review',
                'metadata' => [
                    'status_change_reason' => 'email_marked_sent',
                    'email_marked_sent_by' => $user->name,
                    'status_changed_at' => now()->toISOString(),
                    'waiting_for' => ['client_response', 'consultant_response'],
                    'next_action' => 'monitor_responses'
                ]
            ]);

            Log::info('Task history entry created for waiting for review status - Task: ' . $task->id);
        } catch (\Exception $e) {
            Log::error('Failed to create task history for waiting for review status: ' . $e->getMessage());
        }
    }

    /**
     * Send in-app notifications to managers when email is marked as sent
     */
    private function sendInAppNotificationsToManagers(Task $task, $emailPreparation, $user)
    {
        try {
            $managers = User::whereIn('role', ['admin', 'manager', 'sub-admin'])->get();

            Log::info('Found ' . $managers->count() . ' managers to notify about email marked as sent for task: ' . $task->id);
            foreach ($managers as $manager) {
                Log::info('Manager: ' . $manager->name . ' (' . $manager->email . ') - Role: ' . $manager->role);
            }

            if ($managers->isEmpty()) {
                Log::warning('No managers found to notify about email marked as sent');
                return;
            }

            foreach ($managers as $manager) {
                // Send notification about email being marked as sent
                \App\Models\UnifiedNotification::createTaskNotification(
                    $manager->id,
                    'email_marked_sent',
                    'Email Marked as Sent',
                    $user->name . ' marked confirmation email as sent for task "' . $task->title . '" to: ' . implode(', ', array_filter(array_map('trim', explode(',', $emailPreparation->to_emails)))),
                    [
                        'task_id' => $task->id,
                        'task_title' => $task->title,
                        'sender_id' => $user->id,
                        'sender_name' => $user->name,
                        'email_preparation_id' => $emailPreparation->id,
                        'to_emails' => implode(', ', array_filter(array_map('trim', explode(',', $emailPreparation->to_emails)))),
                        'subject' => $emailPreparation->subject,
                        'action_url' => route('tasks.show', $task->id)
                    ],
                    $task->id,
                    'normal'
                );

                // Send notification about task waiting for review
                \App\Models\UnifiedNotification::createTaskNotification(
                    $manager->id,
                    'task_waiting_for_review',
                    'Task Waiting for Review',
                    'Task "' . $task->title . '" is now waiting for client/consultant review after email was sent to: ' . implode(', ', array_filter(array_map('trim', explode(',', $emailPreparation->to_emails)))),
                    [
                        'task_id' => $task->id,
                        'task_title' => $task->title,
                        'sender_id' => $user->id,
                        'sender_name' => $user->name,
                        'email_preparation_id' => $emailPreparation->id,
                        'to_emails' => implode(', ', array_filter(array_map('trim', explode(',', $emailPreparation->to_emails)))),
                        'subject' => $emailPreparation->subject,
                        'action_url' => route('tasks.show', $task->id)
                    ],
                    $task->id,
                    'normal'
                );

                Log::info('In-app notifications sent to manager: ' . $manager->email . ' for task: ' . $task->id);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send in-app notifications to managers: ' . $e->getMessage());
        }
    }

    /**
     * Show free mail form for users and managers
     */
    public function showFreeMailForm(Task $task)
    {
        // Allow assigned user or managers to send free mail
        if ($task->assigned_to !== Auth::id() && !Auth::user()->isManager()) {
            abort(403, 'Access denied. Only the assigned user or managers can send free mail.');
        }

        return view('tasks.free-mail', compact('task'));
    }

    /**
     * Send free mail via Gmail
     */
    public function sendFreeMail(Request $request, Task $task)
    {
        // Allow assigned user or managers to send free mail
        if ($task->assigned_to !== Auth::id() && !Auth::user()->isManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only the assigned user or managers can send free mail.'
            ], 403);
        }

        $request->validate([
            'to_emails' => 'required|string',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'cc_emails' => 'nullable|string',
            'bcc_emails' => 'nullable|string',
        ]);

        try {
            // Create email preparation record for tracking
            $emailPreparation = $task->emailPreparations()->create([
                'prepared_by' => Auth::id(),
                'to_emails' => $request->to_emails,
                'cc_emails' => $request->cc_emails . (empty($request->cc_emails) ? '' : ',') . 'engineering@orion-contracting.com',
                'bcc_emails' => $request->bcc_emails,
                'subject' => $request->subject,
                'body' => $request->body,
                'status' => 'draft',
                'sent_via' => 'gmail_free_mail',
            ]);

            // Notify managers about the free mail
            $this->notifyManagersAboutFreeMail($task, $emailPreparation);

            Log::info('Free mail prepared for task: ' . $task->id . ' by user: ' . Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Free mail prepared successfully! Opening Gmail...',
                'gmail_url' => $this->generateGmailUrl($request),
                'email_preparation_id' => $emailPreparation->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to prepare free mail for task: ' . $task->id . ' - ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to prepare free mail: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generate Gmail compose URL for free mail
     */
    private function generateGmailUrl(Request $request)
    {
        $user = Auth::user();
        $gmailUrl = new \GuzzleHttp\Psr7\Uri('https://mail.google.com/mail/');

        // Build query parameters
        $queryParams = [
            'view' => 'cm',
            'fs' => '1',
            'to' => $request->to_emails,
            'cc' => $request->cc_emails . (empty($request->cc_emails) ? '' : ',') . 'engineering@orion-contracting.com',
            'bcc' => $request->bcc_emails,
            'su' => $request->subject,
            'body' => strip_tags($request->body), // Convert HTML to plain text for Gmail
        ];

        // Add from parameter to ensure Gmail uses the correct sender account
        if ($user && $user->email) {
            $queryParams['from'] = $user->email;
        }

        $gmailUrl = $gmailUrl->withQuery(http_build_query($queryParams));

        return $gmailUrl->__toString();
    }

    /**
     * Notify managers about free mail
     */
    private function notifyManagersAboutFreeMail(Task $task, $emailPreparation)
    {
        $managers = User::where('role', 'manager')->get();
        $sender = Auth::user();

        foreach ($managers as $manager) {
            $manager->notify(new \App\Notifications\FreeMailSentNotification(
                $task,
                $emailPreparation,
                $sender,
                $emailPreparation->to_emails
            ));
        }
    }

    /**
     * Debug endpoint to check email preparation attachments
     */
    public function debugEmailAttachments(Task $task)
    {
        $emailPreparation = $task->emailPreparations()->where('status', 'draft')->orderBy('id', 'desc')->first();

        if (!$emailPreparation) {
            return response()->json(['error' => 'No email preparation found']);
        }

        $debug = [
            'email_preparation_id' => $emailPreparation->id,
            'attachments_raw' => $emailPreparation->attachments,
            'attachments_count' => is_array($emailPreparation->attachments) ? count($emailPreparation->attachments) : 0,
            'file_checks' => []
        ];

        if ($emailPreparation->attachments && is_array($emailPreparation->attachments)) {
            foreach ($emailPreparation->attachments as $attachmentPath) {
                $fullPath = storage_path('app/' . $attachmentPath);
                $debug['file_checks'][] = [
                    'path' => $attachmentPath,
                    'full_path' => $fullPath,
                    'exists' => file_exists($fullPath),
                    'size' => file_exists($fullPath) ? filesize($fullPath) : 0,
                    'readable' => file_exists($fullPath) ? is_readable($fullPath) : false
                ];
            }
        }

        return response()->json($debug);
    }

    /**
     * Create a default email preparation for a task
     */
    private function createDefaultEmailPreparation(Task $task)
    {
        // Get the assigned user's email as the default recipient
        $defaultToEmail = $task->assignee ? $task->assignee->email : '';

        // Create default email content
        $defaultSubject = "Project Update: Task Completed - {$task->title}";
        $defaultBody = "Dear {$task->assignee->name},\n\n" .
                      "I hope this email finds you well. I am writing to inform you that the assigned task '{$task->title}' has been completed and submitted for review.\n\n" .
                      "Project Information:\n" .
                      "- Project: {$task->project->name}\n" .
                      "- Priority Level: " . ucfirst($task->priority) . "\n" .
                      "- Original Due Date: " . ($task->due_date ? (is_string($task->due_date) ? $task->due_date : $task->due_date->format('M d, Y')) : 'Not specified') . "\n" .
                      "- Completion Date: " . now()->format('M d, Y') . "\n\n" .
                      "The task has been completed according to the specifications and is ready for your review. Please let me know if you need any additional information or modifications.\n\n" .
                      "Thank you for your time and consideration.\n\n" .
                      "Best regards,\n" .
                      Auth::user()->name;

        // Create the email preparation
        $emailPreparation = $task->emailPreparations()->create([
            'prepared_by' => Auth::id(),
            'to_emails' => $defaultToEmail,
            'cc_emails' => '',
            'bcc_emails' => '',
            'subject' => $defaultSubject,
            'body' => $defaultBody,
            'attachments' => [],
            'status' => 'draft',
        ]);

        return $emailPreparation;
    }

    /**
     * Get Gmail connection status for current user
     */
    public function getGmailStatus()
    {
        $user = Auth::user();
        $gmailService = app(\App\Services\GmailOAuthService::class);

        $status = [
            'connected' => $user->hasGmailConnected(),
            'connected_at' => $user->gmail_connected_at,
            'email' => $user->email,
        ];

        if ($user->hasGmailConnected()) {
            $configCheck = $gmailService->checkConfiguration();
            $status['config_valid'] = $configCheck['configured'];
            $status['config_issues'] = $configCheck['issues'];
        }

        return response()->json($status);
    }

    /**
     * Send notification email to engineering@orion-contracting.com via SMTP
     */
    private function sendDesignersNotification(Task $task, $emailPreparation, User $sender)
    {
        try {
            Log::info('Sending designers notification for task: ' . $task->id);

            // Create a copy of the email preparation for designers
            $designersEmailData = [
                'from' => 'engineering@orion-contracting.com',
                'from_name' => 'Orion Engineering System',
                'to' => ['engineering@orion-contracting.com'],
                'subject' => '[NOTIFICATION] ' . $emailPreparation->subject,
                'body' => view('emails.task-confirmation', [
                    'task' => $task,
                    'emailPreparation' => $emailPreparation,
                    'sender' => $sender,
                ])->render(),
                'task_id' => $task->id,
            ];

            // Send via Laravel Mail (SMTP)
            $mail = new \App\Mail\TaskConfirmationMail($task, $emailPreparation, $sender);
            $mail->from('engineering@orion-contracting.com', 'Orion Engineering System');

            Mail::send($mail);

            Log::info('Designers notification sent successfully for task: ' . $task->id);

        } catch (\Exception $e) {
            Log::error('Failed to send designers notification for task: ' . $task->id . ' - ' . $e->getMessage());
            // Don't fail the main email if designers notification fails
        }
    }

    /**
     * Send approval email via Gmail OAuth
     */
private function sendApprovalEmailViaGmail(Task $task, User $approver)
    {
        try {
            $gmailOAuthService = app(\App\Services\GmailOAuthService::class);

            // Prepare email data for Gmail API
            $emailData = [
                'from' => $approver->email,
                'from_name' => $approver->name,
                'to' => [$task->assignee->email],
                'subject' => 'Task Approved: ' . $task->title,
                'body' => view('emails.task-approval-internal', [
                    'task' => $task,
                    'user' => $task->assignee,
                    'approver' => $approver,
                ])->render(),
            ];

            $success = $gmailOAuthService->sendEmail($approver, $emailData);

            if (!$success) {
                Log::error('Gmail OAuth failed for approval email - Email not sent');
                throw new \Exception('Failed to send approval email via Gmail OAuth. Please check your Gmail connection.');
            }

        } catch (\Exception $e) {
            Log::error('Gmail OAuth error for approval email: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Show general email form
     */
    public function showGeneralEmailForm()
    {
        return view('emails.general-email-form');
    }

    /**
     * Send general email using Gmail OAuth (same as confirmation emails)
     */
    public function sendGeneralEmail(Request $request)
    {
        $validated = $request->validate([
            'to_emails' => 'required|string',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        try {
            $user = Auth::user();
            $toEmails = array_filter(array_map('trim', explode(',', $validated['to_emails'])));

            // Check if user has Gmail connected (same as confirmation emails)
            $useGmailOAuth = $user->hasGmailConnected();

            Log::info('Sending general email for user: ' . $user->id . ' - Gmail OAuth: ' . ($useGmailOAuth ? 'Yes' : 'No'));

            // Prepare email data (same structure as confirmation emails)
            $emailData = [
                'from' => $user->email,
                'from_name' => $user->name,
                'to' => $toEmails,
                'subject' => $validated['subject'],
                'body' => view('emails.user-general-email-gmail', [
                    'bodyContent' => $validated['body'],
                    'senderName' => $user->name,
                    'senderEmail' => $user->email,
                    'toRecipients' => $toEmails,
                    'subject' => $validated['subject'],
                ])->render(),
            ];

            $success = false;

            if ($useGmailOAuth) {
                // Use Gmail OAuth for sending email (same as confirmation emails)
                Log::info('Using Gmail OAuth for sending general email - Gmail Only Mode');
                $gmailOAuthService = app(\App\Services\GmailOAuthService::class);

                // Remove engineering@orion-contracting.com from CC for Gmail OAuth
                $gmailEmailData = $emailData;

                $success = $gmailOAuthService->sendEmail($user, $gmailEmailData);

                if ($success) {
                    Log::info('General email sent successfully via Gmail OAuth for user: ' . $user->id);

                    // Send separate email to engineering@orion-contracting.com via SMTP
                    $this->sendGeneralEmailNotification($user, $validated, $toEmails);
                } else {
                    return redirect()->back()->with('error', 'Failed to send email via Gmail OAuth. Please check your Gmail connection.');
                }
            } else {
                // Use Laravel Mail with simple tracking (fallback)
                Log::info('Using simple email tracking for general email - CC to engineering@orion-contracting.com');

                $email = new \App\Mail\UserGeneralEmail(
                    $validated['subject'],
                    $validated['body'],
                    $user,
                    $toEmails
                );

                Mail::to($toEmails)
                    ->cc(['engineering@orion-contracting.com'])
                    ->send($email);

                $success = true;
                Log::info('General email sent successfully via SMTP for user: ' . $user->id);
            }

            if ($success) {
                return redirect()->back()->with('success', 'Email sent successfully from your Gmail account!');
            } else {
                return redirect()->back()->with('error', 'Failed to send email. Please try again.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to send general email: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    /**
     * Move task to waiting for sending client/consultant approval
     */
    public function moveToWaitingSendingApproval(Task $task)
    {
        try {
            $task->moveToWaitingSendingApproval();
            return redirect()->back()->with('success', 'Task moved to waiting for sending client/consultant approval.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Send task for client/consultant approval
     */
    public function sendForClientConsultantApproval(Task $task)
    {
        try {
            $task->sendForClientConsultantApproval();
            return redirect()->back()->with('success', 'Task sent for client/consultant approval.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update client approval status
     */
    public function updateClientApproval(Request $request, Task $task)
    {
        $request->validate([
            'client_status' => 'required|in:not_attached,approved,rejected',
            'client_notes' => 'nullable|string|max:1000'
        ]);

        try {
            $task->updateClientApproval($request->client_status, $request->client_notes);
            return redirect()->back()->with('success', 'Client approval status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update consultant approval status
     */
    public function updateConsultantApproval(Request $request, Task $task)
    {
        $request->validate([
            'consultant_status' => 'required|in:not_attached,approved,rejected',
            'consultant_notes' => 'nullable|string|max:1000'
        ]);

        try {
            $task->updateConsultantApproval($request->consultant_status, $request->consultant_notes);
            return redirect()->back()->with('success', 'Consultant approval status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update internal approval status (manager approval)
     */
    public function updateInternalApproval(Request $request, Task $task)
    {
        // Only managers can update internal approval
        if (!Auth::user()->isManager()) {
            abort(403, 'Access denied. Only managers can update internal approval.');
        }

        $request->validate([
            'internal_status' => 'required|in:pending,approved,rejected',
            'internal_notes' => 'nullable|string|max:1000'
        ]);

        try {
            $task->updateInternalApproval($request->internal_status, $request->internal_notes);
            return redirect()->back()->with('success', 'Internal approval status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Accept task assignment
     */
    public function acceptTask(Task $task)
    {
        // Only assigned user can accept the task
        if ($task->assigned_to !== Auth::id()) {
            abort(403, 'Access denied. Only the assigned user can accept this task.');
        }

        // Only pending or assigned tasks can be accepted
        if (!in_array($task->status, ['pending', 'assigned'])) {
            abort(403, 'Task cannot be accepted in current status.');
        }

        try {
            $task->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            // Add to history
            $task->histories()->create([
                'user_id' => Auth::id(),
                'action' => 'task_accepted',
                'description' => 'User accepted the task and started working on it.',
                'metadata' => ['status_change' => 'assigned_to_in_progress']
            ]);

            // Notify manager
            $this->notifyManagerAboutTaskAcceptance($task, Auth::user());

            return redirect()->back()->with('success', 'Task accepted successfully. You can now start working on it.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to accept task: ' . $e->getMessage());
        }
    }

    /**
     * Submit task for review
     */
    public function submitForReview(Request $request, Task $task)
    {
        // Only assigned user can submit for review
        if ($task->assigned_to !== Auth::id()) {
            abort(403, 'Access denied. Only the assigned user can submit this task for review.');
        }

        // Only in_progress tasks can be submitted for review
        if ($task->status !== 'in_progress') {
            abort(403, 'Task must be in progress to submit for review.');
        }

        $request->validate([
            'completion_notes' => 'required|string|max:2000'
        ]);

        try {
            $task->update([
                'status' => 'submitted_for_review',
                'completion_notes' => $request->completion_notes,
                'submitted_at' => now(),
            ]);

            // Add to history
            $task->histories()->create([
                'user_id' => Auth::id(),
                'action' => 'submitted_for_review',
                'description' => 'Task submitted for review with completion notes.',
                'metadata' => [
                    'status_change' => 'in_progress_to_submitted_for_review',
                    'completion_notes' => $request->completion_notes
                ]
            ]);

            // Notify manager
            $this->notifyManagerAboutTaskSubmission($task, Auth::user(), $request->completion_notes);

            return redirect()->back()->with('success', 'Task submitted for review successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to submit task: ' . $e->getMessage());
        }
    }

    /**
     * Start review (Manager action)
     */
    public function startReview(Request $request, Task $task)
    {
        // Only managers can start review
        if (!Auth::user()->isManager()) {
            abort(403, 'Access denied. Only managers can start review.');
        }

        // Only submitted_for_review tasks can start review
        if ($task->status !== 'submitted_for_review') {
            abort(403, 'Task must be submitted for review to start review.');
        }

        try {
            $task->update([
                'status' => 'in_review',
            ]);

            // Add to history
            $task->histories()->create([
                'user_id' => Auth::id(),
                'action' => 'review_started',
                'description' => 'Manager started reviewing the task.',
                'metadata' => ['status_change' => 'submitted_for_review_to_in_review']
            ]);

            // Notify user
            $this->notifyUserAboutReviewStart($task, Auth::user());

            return redirect()->back()->with('success', 'Review started successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to start review: ' . $e->getMessage());
        }
    }

    /**
     * Update client response status
     */
    public function updateClientResponse(Request $request, Task $task)
    {
        $request->validate([
            'client_response_status' => 'required|in:pending,approved,rejected',
            'client_response_notes' => 'nullable|string|max:2000'
        ]);

        try {
            $task->updateClientResponse($request->client_response_status, $request->client_response_notes);
            return redirect()->back()->with('success', 'Client response updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update consultant response status
     */
    public function updateConsultantResponse(Request $request, Task $task)
    {
        $request->validate([
            'consultant_response_status' => 'required|in:pending,approved,rejected',
            'consultant_response_notes' => 'nullable|string|max:2000'
        ]);

        try {
            $task->updateConsultantResponse($request->consultant_response_status, $request->consultant_response_notes);
            return redirect()->back()->with('success', 'Consultant response updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Finish review - Auto-saves client and consultant responses
     */
    public function finishReview(Request $request, Task $task)
    {
        try {
            // First, save client response if provided
            if ($request->has('client_response_status')) {
                $request->validate([
                    'client_response_status' => 'required|in:pending,approved,rejected',
                    'client_response_notes' => 'nullable|string|max:2000'
                ]);

                $task->updateClientResponse(
                    $request->client_response_status,
                    $request->client_response_notes
                );
            }

            // Then, save consultant response if provided
            if ($request->has('consultant_response_status')) {
                $request->validate([
                    'consultant_response_status' => 'required|in:pending,approved,rejected',
                    'consultant_response_notes' => 'nullable|string|max:2000'
                ]);

                $task->updateConsultantResponse(
                    $request->consultant_response_status,
                    $request->consultant_response_notes
                );
            }

            // Finally, finish the review and notify manager
            $task->finishReview();

            return redirect()->back()->with('success', 'Client and consultant responses saved. Review finished successfully. Manager has been notified.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Manager override (reject or reset for review)
     */
    public function managerOverride(Request $request, Task $task)
    {
        // Only managers can override
        if (!Auth::user()->isManager()) {
            abort(403, 'Access denied. Only managers can override task status.');
        }

        $request->validate([
            'manager_override_status' => 'required|in:reject,reset_for_review',
            'manager_override_notes' => 'nullable|string|max:2000'
        ]);

        try {
            $task->managerOverride($request->manager_override_status, $request->manager_override_notes);
            return redirect()->back()->with('success', 'Manager override applied successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Manager marks task as completed after client/consultant review
     */
    public function markAsCompleted(Request $request, Task $task)
    {
        // Only managers can mark as completed
        if (!Auth::user()->isManager()) {
            abort(403, 'Access denied. Only managers can mark tasks as completed.');
        }

        // Only tasks in review after client/consultant reply can be marked as completed
        if ($task->status !== 'in_review_after_client_consultant_reply') {
            abort(403, 'Task must be in review after client/consultant reply to be marked as completed.');
        }

        $request->validate([
            'completion_notes' => 'nullable|string|max:2000'
        ]);

        try {
            $task->markAsCompleted($request->completion_notes);
            return redirect()->back()->with('success', 'Task marked as completed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Manager requires task to be re-submitted by user
     */
    public function requireResubmit(Request $request, Task $task)
    {
        // Only managers can require resubmission
        if (!Auth::user()->isManager()) {
            abort(403, 'Access denied. Only managers can require task resubmission.');
        }

        // Only tasks in review after client/consultant reply can be sent for resubmission
        if ($task->status !== 'in_review_after_client_consultant_reply') {
            abort(403, 'Task must be in review after client/consultant reply to require resubmission.');
        }

        $request->validate([
            'resubmit_notes' => 'required|string|max:2000'
        ]);

        try {
            $task->requireResubmit($request->resubmit_notes);
            return redirect()->back()->with('success', 'Task sent back for resubmission.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * User re-submits task after manager requested changes
     */
    public function resubmitTask(Request $request, Task $task)
    {
        // Only assigned user can resubmit
        if ($task->assigned_to !== Auth::id()) {
            abort(403, 'Access denied. Only the assigned user can resubmit this task.');
        }

        // Only tasks requiring resubmission can be resubmitted
        if ($task->status !== 're_submit_required') {
            abort(403, 'Task is not in resubmission required status.');
        }

        try {
            $task->resubmitTask();
            return redirect()->back()->with('success', 'Task resubmitted for review successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Send notification email to engineering@orion-contracting.com via SMTP
     * (same as confirmation emails)
     */
    private function sendGeneralEmailNotification($user, $emailData, $toEmails)
    {
        try {
            Log::info('Sending general email notification to engineering@orion-contracting.com');

            // Create a copy of the email for engineering notification
            $engineeringEmailData = [
                'from' => 'engineering@orion-contracting.com',
                'from_name' => 'Orion Engineering System',
                'to' => ['engineering@orion-contracting.com'],
                'subject' => '[NOTIFICATION] ' . $emailData['subject'],
                'body' => view('emails.user-general-email-gmail', [
                    'bodyContent' => $emailData['body'],
                    'senderName' => $user->name,
                    'senderEmail' => $user->email,
                    'toRecipients' => $toEmails,
                    'subject' => $emailData['subject'],
                ])->render(),
            ];

            // Send via Laravel Mail (SMTP)
            $mail = new \App\Mail\UserGeneralEmail(
                $emailData['subject'],
                $emailData['body'],
                $user,
                $toEmails
            );
            $mail->from('engineering@orion-contracting.com', 'Orion Engineering System');

            Mail::send($mail);

            Log::info('General email notification sent successfully to engineering@orion-contracting.com');

        } catch (\Exception $e) {
            Log::error('Failed to send general email notification: ' . $e->getMessage());
            // Don't fail the main email if notification fails
        }
    }

    /**
     * Notify managers when a user sends a confirmation email
     */
    private function notifyManagersAboutConfirmationEmail(Task $task, User $sender)
    {
        try {
            // Get all managers
            $managers = User::where('role', 'admin')->get();

            foreach ($managers as $manager) {
                // Skip if the sender is also a manager
                if ($manager->id === $sender->id) {
                    continue;
                }

                // Create notification
                $notification = new \App\Models\UnifiedNotification([
                    'user_id' => $manager->id,
                    'category' => 'task',
                    'type' => 'task_confirmation_email_sent',
                    'title' => 'Confirmation Email Sent',
                    'message' => "User {$sender->name} has sent a confirmation email for task: {$task->title}",
                    'task_id' => $task->id,
                    'data' => [
                        'task_id' => $task->id,
                        'sender_id' => $sender->id,
                        'sender_name' => $sender->name,
                        'task_title' => $task->title,
                        'project_name' => $task->project->name ?? 'Unknown Project'
                    ],
                    'is_read' => false
                ]);
                $notification->save();

                // Add to task history
                $task->histories()->create([
                    'user_id' => $sender->id,
                    'action' => 'confirmation_email_sent',
                    'description' => "Confirmation email sent to clients/consultants. Manager {$manager->name} notified.",
                    'metadata' => [
                        'notification_id' => $notification->id,
                        'manager_id' => $manager->id
                    ]
                ]);
            }

            Log::info("Managers notified about confirmation email for task: {$task->id} by user: {$sender->id}");
        } catch (\Exception $e) {
            Log::error("Failed to notify managers about confirmation email: " . $e->getMessage());
        }
    }

    /**
     * Notify manager when user accepts task
     */
    private function notifyManagerAboutTaskAcceptance(Task $task, User $user)
    {
        try {
            // Get task creator (manager)
            $manager = $task->creator;
            if (!$manager || $manager->id === $user->id) return;

            $notification = new \App\Models\UnifiedNotification([
                'user_id' => $manager->id,
                'category' => 'task',
                'type' => 'task_accepted',
                'title' => 'Task Accepted',
                'message' => "User {$user->name} has accepted task: {$task->title}",
                'task_id' => $task->id,
                'data' => [
                    'task_id' => $task->id,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'task_title' => $task->title,
                    'project_name' => $task->project->name ?? 'Unknown Project'
                ],
                'is_read' => false
            ]);
            $notification->save();

            Log::info("Manager notified about task acceptance for task: {$task->id} by user: {$user->id}");
        } catch (\Exception $e) {
            Log::error("Failed to notify manager about task acceptance: " . $e->getMessage());
        }
    }

    /**
     * Notify manager when user submits task for review
     */
    private function notifyManagerAboutTaskSubmission(Task $task, User $user, $completionNotes)
    {
        try {
            // Get task creator (manager)
            $manager = $task->creator;
            if (!$manager || $manager->id === $user->id) return;

            $notification = new \App\Models\UnifiedNotification([
                'user_id' => $manager->id,
                'category' => 'task',
                'type' => 'task_submitted_for_review',
                'title' => 'Task Submitted for Review',
                'message' => "User {$user->name} has submitted task: {$task->title} for review",
                'task_id' => $task->id,
                'data' => [
                    'task_id' => $task->id,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'task_title' => $task->title,
                    'project_name' => $task->project->name ?? 'Unknown Project',
                    'completion_notes' => $completionNotes
                ],
                'is_read' => false
            ]);
            $notification->save();

            Log::info("Manager notified about task submission for task: {$task->id} by user: {$user->id}");
        } catch (\Exception $e) {
            Log::error("Failed to notify manager about task submission: " . $e->getMessage());
        }
    }

    /**
     * Notify user when manager starts review
     */
    private function notifyUserAboutReviewStart(Task $task, User $manager)
    {
        try {
            $user = $task->assignee;
            if (!$user || $user->id === $manager->id) return;

            $notification = new \App\Models\UnifiedNotification([
                'user_id' => $user->id,
                'category' => 'task',
                'type' => 'review_started',
                'title' => 'Review Started',
                'message' => "Manager {$manager->name} has started reviewing your task: {$task->title}",
                'task_id' => $task->id,
                'data' => [
                    'task_id' => $task->id,
                    'manager_id' => $manager->id,
                    'manager_name' => $manager->name,
                    'task_title' => $task->title,
                    'project_name' => $task->project->name ?? 'Unknown Project'
                ],
                'is_read' => false
            ]);
            $notification->save();

            Log::info("User notified about review start for task: {$task->id} by manager: {$manager->id}");
        } catch (\Exception $e) {
            Log::error("Failed to notify user about review start: " . $e->getMessage());
        }
    }
}


