<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectFolder;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskHistory;
use App\Models\CustomNotification;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
            foreach ($request->contractors as $contractorId) {
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

    // New workflow methods
    public function acceptTask(Task $task)
    {
        // Only the assigned user can accept the task
        if ($task->assigned_to !== Auth::id()) {
            abort(403, 'Access denied. You can only accept tasks assigned to you.');
        }

        try {
            $task->acceptTask();
            return redirect()->back()->with('success', 'Task accepted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function submitForReview(Request $request, Task $task)
    {
        // Only the assigned user can submit for review
        if ($task->assigned_to !== Auth::id()) {
            abort(403, 'Access denied. You can only submit tasks assigned to you.');
        }

        $validated = $request->validate([
            'completion_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $task->submitForReview($validated['completion_notes'] ?? null);
            return redirect()->back()->with('success', 'Task submitted for review successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

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
            'attachments.*' => 'file|max:10240', // 10MB max per file
        ]);

        try {
            // Handle file uploads
            $attachmentPaths = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('email-attachments');
                    $attachmentPaths[] = $path;
                }
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

            return redirect()->back()->with('success', 'Email preparation saved successfully!');
        } catch (\Exception $e) {
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
                'attachments.*' => 'file|max:10240',
            ]);

            // Handle file uploads
            $attachmentPaths = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('email-attachments');
                    $attachmentPaths[] = $path;
                }
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

            // Check if user has Gmail connected and use Gmail OAuth if available
            $useGmailOAuth = $user->hasGmailConnected();

            Log::info('Email sending attempt - Current User: ' . $user->id . ' (' . $user->email . '), Gmail Only Mode: ' . ($useGmailOAuth ? 'Yes' : 'No'));

            // Log email preparation details
            Log::info('Email preparation - To: ' . $emailPreparation->to_emails . ', CC: ' . ($emailPreparation->cc_emails ?? 'none') . ', BCC: ' . ($emailPreparation->bcc_emails ?? 'none') . ', Subject: ' . $emailPreparation->subject);

            // Parse email addresses
            $toEmails = array_filter(array_map('trim', explode(',', $emailPreparation->to_emails)));
            $ccEmails = $emailPreparation->cc_emails ? array_filter(array_map('trim', explode(',', $emailPreparation->cc_emails))) : [];
            $bccEmails = $emailPreparation->bcc_emails ? array_filter(array_map('trim', explode(',', $emailPreparation->bcc_emails))) : [];

            // Always add engineering@orion-contracting.com to CC
            if (!in_array('engineering@orion-contracting.com', $ccEmails)) {
                $ccEmails[] = 'engineering@orion-contracting.com';
            }

            // NEW: Add all users (role: 'user') to CC so they get notifications
            $users = User::where('role', 'user')->get();
            foreach ($users as $userToNotify) {
                if (!in_array($userToNotify->email, $ccEmails)) {
                    $ccEmails[] = $userToNotify->email;
                }
            }

            // Prepare email data
            $emailData = [
                'from' => $user->email,
                'from_name' => $user->name,
                'to' => $toEmails,
                'subject' => $emailPreparation->subject,
                'body' => view('emails.task-confirmation', [
                    'task' => $task,
                    'emailPreparation' => $emailPreparation,
                    'sender' => $user,
                ])->render(),
                'task_id' => $task->id,
            ];

            if (!empty($ccEmails)) {
                $emailData['cc'] = $ccEmails;
            }

            if (!empty($bccEmails)) {
                $emailData['bcc'] = $bccEmails;
            }

            $success = false;
            $trackedEmail = null;

            if ($useGmailOAuth) {
                // Use Gmail OAuth for sending email to main recipients
                Log::info('Using Gmail OAuth for sending email - Gmail Only Mode');
                $gmailOAuthService = app(\App\Services\GmailOAuthService::class);

                // Remove engineering@orion-contracting.com from CC for Gmail OAuth
                $gmailEmailData = $emailData;
                if (isset($gmailEmailData['cc'])) {
                    $gmailEmailData['cc'] = array_filter($gmailEmailData['cc'], function($email) {
                        return $email !== 'engineering@orion-contracting.com';
                    });
                }

                $success = $gmailOAuthService->sendEmail($user, $gmailEmailData);

                if ($success) {
                    Log::info('Confirmation email sent successfully for task: ' . $task->id . ' by user: ' . Auth::id() . ' via Gmail OAuth');

                    // Send separate email to engineering@orion-contracting.com via SMTP
                    $this->sendDesignersNotification($task, $emailPreparation, $user);
                } else {
                    Log::error('Gmail OAuth failed for user: ' . $user->id . ' - Email not sent');
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to send email via Gmail OAuth. Please check your Gmail connection.',
                        'redirect_url' => route('tasks.show', $task->id)
                    ], 400);
                }
            } else {
                // Use Laravel Mail with simple tracking
                Log::info('Using simple email tracking - CC to engineering@orion-contracting.com');

                // Create the mail instance
                $mail = new \App\Mail\TaskConfirmationMail($task, $emailPreparation, $user);

                // Set CC and BCC on the mail instance
                if (!empty($ccEmails)) {
                    $mail->cc($ccEmails);
                }
                if (!empty($bccEmails)) {
                    $mail->bcc($bccEmails);
                }

                // Send the email
                Mail::send($mail);

                // Track the sent email
                $simpleEmailTrackingService = app(\App\Services\SimpleEmailTrackingService::class);
                $trackedEmail = $simpleEmailTrackingService->trackSentEmail($user, $emailData);
                $success = $trackedEmail !== null;

                if (!$success) {
                    Log::error('Email tracking failed for user: ' . $user->id . ' - Email may have been sent but not tracked');
                    return response()->json([
                        'success' => false,
                        'message' => 'Email may have been sent but tracking failed. Please check your email configuration.',
                        'redirect_url' => route('tasks.show', $task->id)
                    ], 400);
                }

                Log::info('Confirmation email sent successfully for task: ' . $task->id . ' by user: ' . Auth::id() . ' with CC to engineering@orion-contracting.com');
            }

            // Update email preparation status
            $emailPreparation->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // Update task status to completed
            $task->update(['status' => 'completed']);

            $message = $useGmailOAuth ?
                'Confirmation email sent successfully via Gmail OAuth!' :
                'Confirmation email sent successfully with tracking enabled!';

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect_url' => route('tasks.show', $task->id)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send confirmation email for task: ' . $task->id . ' - ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
                'redirect_url' => route('tasks.show', $task->id)
            ]);
        }
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
                      "- Original Due Date: " . ($task->due_date ? $task->due_date->format('M d, Y') : 'Not specified') . "\n" .
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
}


