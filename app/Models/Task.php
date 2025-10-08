<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\ExternalStakeholder;
use App\Models\TaskNotification;
use App\Models\UnifiedNotification;
use App\Mail\TaskNotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'folder_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'due_date',
        'status',
        'priority',
        'assigned_at',
        'started_at',
        'completed_at',
        'completion_notes',
        'accepted_at',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'approval_notes',
        'rejection_notes',
        'client_status',
        'client_notes',
        'client_updated_at',
        'consultant_status',
        'consultant_notes',
        'consultant_updated_at',
        'combined_approval_status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'client_updated_at' => 'datetime',
        'consultant_updated_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function folder()
    {
        return $this->belongsTo(ProjectFolder::class, 'folder_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function histories()
    {
        return $this->hasMany(TaskHistory::class);
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function customNotifications()
    {
        return $this->hasMany(CustomNotification::class, 'data->task_id');
    }

    public function emailPreparations()
    {
        return $this->hasMany(TaskEmailPreparation::class);
    }

    public function contractors()
    {
        return $this->belongsToMany(Contractor::class, 'contractor_task')
                    ->withPivot('role', 'added_at')
                    ->withTimestamps();
    }

    public function clients()
    {
        return $this->contractors()->where('contractors.type', 'client');
    }

    public function consultants()
    {
        return $this->contractors()->where('contractors.type', 'consultant');
    }

    // Methods to manage task contractors
    public function addContractor(Contractor $contractor, string $role = 'participant')
    {
        if (!$this->contractors()->where('contractor_id', $contractor->id)->exists()) {
            $this->contractors()->attach($contractor->id, [
                'role' => $role,
                'added_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    public function removeContractor(Contractor $contractor)
    {
        $this->contractors()->detach($contractor->id);
    }

    public function updateContractorRole(Contractor $contractor, string $role)
    {
        $this->contractors()->updateExistingPivot($contractor->id, [
            'role' => $role,
            'updated_at' => now()
        ]);
    }

    // Get approved tasks for specific contractor type and project
    public static function getApprovedTasksForContractorType($contractorType, $projectId = null)
    {
        $query = self::whereHas('contractors', function($q) use ($contractorType) {
            $q->where('contractors.type', $contractorType);
        })->where('status', 'completed');

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        return $query->get();
    }

    // Get approved tasks for specific contractor
    public static function getApprovedTasksForContractor(Contractor $contractor, $projectId = null)
    {
        $query = self::whereHas('contractors', function($q) use ($contractor) {
            $q->where('contractor_id', $contractor->id);
        })->where('status', 'completed');

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        return $query->get();
    }

    // Status management methods
    public function assignTo(User $user)
    {
        $this->update([
            'assigned_to' => $user->id,
            'assigned_at' => now(),
            'status' => 'assigned'
        ]);

        // Create history record
        $this->histories()->create([
            'user_id' => Auth::id() ?? 1, // Fallback for testing
            'action' => 'assigned',
            'new_value' => $user->name,
            'description' => "Task assigned to {$user->name}",
            'metadata' => ['assigned_to' => $user->id]
        ]);

        // Send notification to assigned user
        $this->sendNotification($user, 'task_assigned', 'Task Assigned', "You have been assigned a new task: {$this->title}");

        // Notify managers about the assignment
        $this->notifyManagers('task_assigned', 'Task Assigned', "Task '{$this->title}' assigned to {$user->name}");
    }

    public function changeStatus(string $status, string $notes = null)
    {
        $oldStatus = $this->status;
        $currentUser = Auth::user();

        $updateData = ['status' => $status];

        if ($status === 'in_progress' && !$this->started_at) {
            $updateData['started_at'] = now();
        }

        if (in_array($status, ['completed', 'approved']) && !$this->completed_at) {
            $updateData['completed_at'] = now();
        }

        if ($notes) {
            $updateData['completion_notes'] = $notes;
        }

        $this->update($updateData);

        // Create history record with manager override indication
        $description = "Status changed from {$oldStatus} to {$status}";
        if ($currentUser && $currentUser->isManager()) {
            $description .= " (Manager Override)";
        }
        if ($notes) {
            $description .= ": {$notes}";
        }

        $this->histories()->create([
            'user_id' => Auth::id() ?? 1, // Fallback for testing
            'action' => 'status_changed',
            'old_value' => $oldStatus,
            'new_value' => $status,
            'description' => $description,
            'metadata' => ['notes' => $notes, 'manager_override' => $currentUser && $currentUser->isManager()]
        ]);

        // Send notification to managers with more specific messaging
        $statusMessages = [
            'pending' => 'Task is pending',
            'assigned' => 'Task has been assigned',
            'in_progress' => 'Task is now in progress',
            'submitted_for_review' => 'Task has been submitted for review',
            'approved' => 'Task has been approved',
            'rejected' => 'Task has been rejected',
            'waiting_sending_client_consultant_approve' => 'Waiting to send for client and consultant approval',
            'waiting_client_consultant_approve' => 'Waiting for client and consultant approval',
            'completed' => 'Task has been completed',
            'cancelled' => 'Task has been cancelled'
        ];

        $statusMessage = $statusMessages[$status] ?? "Task status changed to {$status}";

        // Add manager override indication to notification
        if ($currentUser && $currentUser->isManager()) {
            $statusMessage .= " (Manager Override)";
        }

        // Notify managers about the status change
        $this->notifyManagers('task_status_changed', 'Task Status Changed', "Task '{$this->title}' - {$statusMessage}" . ($notes ? ". Notes: {$notes}" : ""));

        // Notify the assigned user about the status change (if they exist and are not the current user)
        if ($this->assignee && $this->assignee->id !== Auth::id()) {
            $userMessage = "Your task '{$this->title}' status has been changed to: " . ucfirst(str_replace('_', ' ', $status));
            if ($notes) {
                $userMessage .= ". Notes: {$notes}";
            }
            if ($currentUser && $currentUser->isManager()) {
                $userMessage .= " (Changed by Manager)";
            }

            $this->sendNotification($this->assignee, 'task_status_changed', 'Task Status Updated', $userMessage);
        }
    }

    private function sendNotification(User $user, string $type, string $title, string $message)
    {
        UnifiedNotification::createTaskNotification(
            $user->id,
            $type,
            $title,
            $message,
            [
                'task_id' => $this->id,
                'project_id' => $this->project_id,
                'due_date' => $this->due_date ? $this->due_date->format('Y-m-d') : null
            ],
            $this->id,
            'normal'
        );
    }

    private function notifyManagers(string $type, string $title, string $message)
    {
        $managers = User::where('role', 'admin')->orWhere('role', 'manager')->get();
        $currentUserId = Auth::id();

        foreach ($managers as $manager) {
            // Don't send notification to the current user (manager who performed the action)
            if ($manager->id !== $currentUserId) {
                $this->sendNotification($manager, $type, $title, $message);
            }
        }
    }

    // Task statistics methods
    public function getCompletionTimeAttribute()
    {
        if ($this->status === 'completed' && $this->completed_at) {
            $startDate = $this->assigned_at ?? $this->created_at;
            return $startDate ? $startDate->diffInDays($this->completed_at) : null;
        }
        return null;
    }

    public function getDaysRemainingAttribute()
    {
        if ($this->status !== 'completed' && $this->due_date) {
            $now = now();
            if ($this->due_date && $this->due_date->isFuture()) {
                return ceil($this->due_date->diffInDays($now));
            } else {
                return $this->due_date ? -ceil($this->due_date->diffInDays($now)) : null; // Negative for overdue
            }
        }
        return null;
    }

    public function getIsOverdueAttribute()
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'completed';
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'pending' => 'bg-secondary',
            'assigned' => 'bg-info',
            'accepted' => 'bg-primary',
            'in_progress' => 'bg-warning',
            'workingon' => 'bg-warning',
            'submitted_for_review' => 'bg-primary',
            'in_review' => 'bg-warning',
            'approved' => 'bg-success',
            'ready_for_email' => 'bg-info',
            'rejected' => 'bg-danger',
            'waiting_sending_client_consultant_approve' => 'bg-warning',
            'waiting_client_consultant_approve' => 'bg-info',
            'completed' => 'bg-success',
            default => 'bg-secondary'
        };
    }

    public function getPriorityBadgeClassAttribute()
    {
        return match($this->priority) {
            'low' => 'bg-success',
            'normal' => 'bg-primary',
            'medium' => 'bg-info',
            'high' => 'bg-warning',
            'urgent' => 'bg-danger',
            'critical' => 'bg-dark',
            default => 'bg-primary'
        };
    }

    // New workflow methods
    public function acceptTask()
    {
        if ($this->status !== 'assigned') {
            throw new \Exception('Only assigned tasks can be accepted');
        }

        $this->update([
            'status' => 'in_progress',
            'accepted_at' => now(),
            'started_at' => now()
        ]);

        // Create history record
        $this->histories()->create([
            'user_id' => Auth::id(),
            'action' => 'accepted',
            'description' => "Task accepted and work started by {$this->assignee->name}",
            'metadata' => ['accepted_at' => now(), 'started_at' => now()]
        ]);

        // Notify managers that task is now in progress
        $this->notifyManagers('task_accepted', 'Task Accepted', "Task '{$this->title}' has been accepted and work has started by {$this->assignee->name}");

        // Notify external stakeholders
        $this->notifyExternalStakeholders('in_progress', 'Task Started', "Task '{$this->title}' has been accepted and work is now in progress.");
    }

    public function submitForReview($notes = null)
    {
        if (!in_array($this->status, ['in_progress', 'rejected'])) {
            throw new \Exception('Only tasks in progress or rejected tasks can be submitted for review');
        }

        $this->update([
            'status' => 'submitted_for_review',
            'submitted_at' => now(),
            'completion_notes' => $notes
        ]);

        // Create history record
        $this->histories()->create([
            'user_id' => Auth::id(),
            'action' => 'submitted_for_review',
            'description' => "Task submitted for review by {$this->assignee->name}",
            'metadata' => ['submitted_at' => now(), 'notes' => $notes]
        ]);

        // Notify managers
        $this->notifyManagers('task_submitted_for_review', 'Task Submitted for Review', "Task '{$this->title}' has been submitted for review by {$this->assignee->name}" . ($notes ? ". Notes: {$notes}" : ""));

        // Notify external stakeholders
        $this->notifyExternalStakeholders('submitted_for_review', 'Task Submitted for Review', "Task '{$this->title}' has been completed and submitted for review.");
    }

    public function approveTask($notes = null)
    {
        // Refresh the model to get the latest status from database
        $this->refresh();

        // Debug: Log the current status before validation
        \Log::info('Task approval attempt', [
            'task_id' => $this->id,
            'current_status' => $this->status,
            'status_type' => gettype($this->status),
            'status_length' => strlen($this->status ?? ''),
            'status_trimmed' => trim($this->status ?? ''),
            'status_equals' => $this->status === 'submitted_for_review',
            'status_in_array' => in_array($this->status, ['submitted_for_review']),
        ]);

        if ($this->status !== 'submitted_for_review') {
            \Log::error('Task approval failed - status validation', [
                'task_id' => $this->id,
                'current_status' => $this->status,
                'expected_status' => 'submitted_for_review',
                'status_match' => $this->status === 'submitted_for_review',
            ]);
            throw new \Exception('Only tasks submitted for review can be approved');
        }

        $this->update([
            'status' => 'approved', // Changed from 'ready_for_email' to 'approved' for production compatibility
            'approved_at' => now(),
            'completed_at' => now(),
            'approval_notes' => $notes
        ]);

        // Create history record
        $this->histories()->create([
            'user_id' => Auth::id(),
            'action' => 'approved',
            'description' => "Task approved by " . Auth::user()->name,
            'metadata' => ['approved_at' => now(), 'notes' => $notes]
        ]);

        // Notify assigned user
        $this->sendNotification($this->assignee, 'task_approved', 'Task Approved', "Your task '{$this->title}' has been approved!");

        // Send email to assigned user (internal) - GMAIL ONLY
        if ($this->assignee && $this->assignee->email) {
            try {
                $approver = Auth::user();

                // FORCE GMAIL ONLY - No SMTP fallback
                if (!$approver || !$approver->hasGmailConnected()) {
                    \Log::error('Gmail OAuth required for approval email but approver does not have Gmail connected');
                    throw new \Exception('Gmail OAuth is required for sending approval emails. Please connect your Gmail account first.');
                }

                $this->sendApprovalEmailViaGmail($approver);
                \Log::info('Approval email sent to assigned user: ' . $this->assignee->email . ' via Gmail OAuth only');
            } catch (\Exception $e) {
                \Log::error('Failed to send approval email to assigned user: ' . $e->getMessage());
                throw $e; // Re-throw to prevent task approval if email fails
            }
        }

        // Notify external stakeholders
        $this->notifyExternalStakeholders('approved', 'Task Approved', "Task '{$this->title}' has been approved and completed successfully.");

        // Auto-create email preparation for confirmation emails
        $this->createDefaultEmailPreparation();
    }

    public function rejectTask($notes = null)
    {
        if ($this->status !== 'submitted_for_review') {
            throw new \Exception('Only tasks submitted for review can be rejected');
        }

        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_notes' => $notes
        ]);

        // Create history record
        $this->histories()->create([
            'user_id' => Auth::id(),
            'action' => 'rejected',
            'description' => "Task rejected by " . Auth::user()->name,
            'metadata' => ['rejected_at' => now(), 'notes' => $notes]
        ]);

        // Notify assigned user
        $this->sendNotification($this->assignee, 'task_rejected', 'Task Rejected', "Your task '{$this->title}' has been rejected. Please review the feedback and resubmit.");

        // Notify external stakeholders
        $this->notifyExternalStakeholders('rejected', 'Task Rejected', "Task '{$this->title}' has been rejected and requires revision.");
    }

    /**
     * Move task to waiting for client/consultant approval after manager approval
     */
    public function moveToWaitingSendingApproval()
    {
        if ($this->status !== 'approved') {
            throw new \Exception('Only approved tasks can be moved to waiting for sending approval');
        }

        $this->update([
            'status' => 'waiting_sending_client_consultant_approve',
        ]);

        // Create history record
        $this->histories()->create([
            'action' => 'waiting_sending_client_consultant_approve',
            'description' => "Task moved to waiting for sending client and consultant approval",
            'metadata' => ['moved_at' => now()]
        ]);

        // Notify assigned user
        $this->sendNotification($this->assignee, 'task_waiting_sending_approval', 'Task Ready for Client/Consultant Approval', "Your task '{$this->title}' is ready to send for client and consultant approval.");

        // Notify managers
        $this->notifyManagers('task_waiting_sending_approval', 'Task Ready for Client/Consultant Approval', "Task '{$this->title}' is ready to send for client and consultant approval.");
    }

    /**
     * Send email to client and consultant and move to waiting status
     */
    public function sendForClientConsultantApproval()
    {
        if ($this->status !== 'waiting_sending_client_consultant_approve') {
            throw new \Exception('Only tasks waiting to send approval can be sent for client/consultant approval');
        }

        $this->update([
            'status' => 'waiting_client_consultant_approve',
        ]);

        // Create history record
        $this->histories()->create([
            'action' => 'sent_for_client_consultant_approval',
            'description' => "Task sent for client and consultant approval",
            'metadata' => ['sent_at' => now()]
        ]);

        // Notify assigned user
        $this->sendNotification($this->assignee, 'task_sent_for_approval', 'Task Sent for Client/Consultant Approval', "Your task '{$this->title}' has been sent for client and consultant approval.");

        // Notify managers
        $this->notifyManagers('task_sent_for_approval', 'Task Sent for Client/Consultant Approval', "Task '{$this->title}' has been sent for client and consultant approval.");
    }

    /**
     * Update client approval status
     */
    public function updateClientApproval($status, $notes = null)
    {
        if (!in_array($status, ['not_attached', 'approved', 'rejected'])) {
            throw new \Exception('Invalid client status');
        }

        $this->update([
            'client_status' => $status,
            'client_notes' => $notes,
            'client_updated_at' => now(),
        ]);

        $this->updateCombinedApprovalStatus();

        // Create history record
        $this->histories()->create([
            'action' => 'client_approval_updated',
            'description' => "Client approval updated to: {$status}" . ($notes ? ". Notes: {$notes}" : ""),
            'metadata' => ['client_status' => $status, 'notes' => $notes, 'updated_at' => now()]
        ]);

        // Check if both approvals are complete
        $this->checkApprovalCompletion();
    }

    /**
     * Update consultant approval status
     */
    public function updateConsultantApproval($status, $notes = null)
    {
        if (!in_array($status, ['not_attached', 'approved', 'rejected'])) {
            throw new \Exception('Invalid consultant status');
        }

        $this->update([
            'consultant_status' => $status,
            'consultant_notes' => $notes,
            'consultant_updated_at' => now(),
        ]);

        $this->updateCombinedApprovalStatus();

        // Create history record
        $this->histories()->create([
            'action' => 'consultant_approval_updated',
            'description' => "Consultant approval updated to: {$status}" . ($notes ? ". Notes: {$notes}" : ""),
            'metadata' => ['consultant_status' => $status, 'notes' => $notes, 'updated_at' => now()]
        ]);

        // Check if both approvals are complete
        $this->checkApprovalCompletion();
    }

    /**
     * Update combined approval status
     */
    private function updateCombinedApprovalStatus()
    {
        $clientStatus = $this->client_status ?? 'not_attached';
        $consultantStatus = $this->consultant_status ?? 'not_attached';

        $combinedStatus = "client-{$clientStatus}-consultant-{$consultantStatus}";

        $this->update(['combined_approval_status' => $combinedStatus]);
    }

    /**
     * Check if both approvals are complete and update task status
     */
    private function checkApprovalCompletion()
    {
        $clientStatus = $this->client_status ?? 'not_attached';
        $consultantStatus = $this->consultant_status ?? 'not_attached';

        // If both are approved, mark as completed
        if ($clientStatus === 'approved' && $consultantStatus === 'approved') {
            $this->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Create history record
            $this->histories()->create([
                'action' => 'completed',
                'description' => "Task completed after client and consultant approval",
                'metadata' => ['completed_at' => now()]
            ]);

            // Notify assigned user
            $this->sendNotification($this->assignee, 'task_completed', 'Task Completed', "Your task '{$this->title}' has been completed after client and consultant approval.");

            // Notify managers
            $this->notifyManagers('task_completed', 'Task Completed', "Task '{$this->title}' has been completed after client and consultant approval.");
        }
        // If either is rejected, mark as rejected
        elseif ($clientStatus === 'rejected' || $consultantStatus === 'rejected') {
            $this->update([
                'status' => 'rejected',
                'rejected_at' => now(),
            ]);

            // Create history record
            $this->histories()->create([
                'action' => 'rejected',
                'description' => "Task rejected by client or consultant",
                'metadata' => ['rejected_at' => now()]
            ]);

            // Notify assigned user
            $this->sendNotification($this->assignee, 'task_rejected', 'Task Rejected', "Your task '{$this->title}' has been rejected by client or consultant.");

            // Notify managers
            $this->notifyManagers('task_rejected', 'Task Rejected', "Task '{$this->title}' has been rejected by client or consultant.");
        }
    }

    public function notifyExternalStakeholders($type, $subject, $message)
    {
        $stakeholders = ExternalStakeholder::active()->get();

        foreach ($stakeholders as $stakeholder) {
            // Create notification record
            $notification = $this->taskNotifications()->create([
                'external_stakeholder_id' => $stakeholder->id,
                'notification_type' => $type,
                'email_subject' => $subject,
                'email_content' => $this->buildEmailContent($stakeholder, $type, $message)
            ]);

            // Send email
            try {
                Mail::to($stakeholder->email)
                    ->cc('engineering@orion-contracting.com')
                    ->send(new TaskNotificationMail($this, $stakeholder, $type, $message));

                // Update notification status
                $notification->update([
                    'status' => 'sent',
                    'sent_at' => now()
                ]);
            } catch (\Exception $e) {
                // Update notification status as failed
                $notification->update([
                    'status' => 'failed'
                ]);

                // Log the error
                \Log::error('Failed to send task notification email: ' . $e->getMessage());
            }
        }
    }

    private function buildEmailContent($stakeholder, $type, $message)
    {
        $content = "
        <html>
        <body>
            <h2>Task Update Notification</h2>
            <p>Dear {$stakeholder->name},</p>
            <p>{$message}</p>

            <h3>Task Details:</h3>
            <ul>
                <li><strong>Title:</strong> {$this->title}</li>
                <li><strong>Description:</strong> {$this->description}</li>
                <li><strong>Project:</strong> {$this->project->name}</li>
                <li><strong>Assigned To:</strong> {$this->assignee->name}</li>
                <li><strong>Due Date:</strong> " . ($this->due_date ? $this->due_date->format('M d, Y') : 'Not set') . "</li>
                <li><strong>Priority:</strong> " . ucfirst($this->priority) . "</li>
                <li><strong>Status:</strong> " . ucfirst(str_replace('_', ' ', $this->status)) . "</li>
            </ul>

            <p>You can view more details by logging into the system.</p>

            <p>Best regards,<br>Task Management System</p>
        </body>
        </html>
        ";

        return $content;
    }

    public function taskNotifications()
    {
        return $this->hasMany(TaskNotification::class);
    }

    /**
     * Create a default email preparation for this task
     */
    public function createDefaultEmailPreparation()
    {
        // Check if email preparation already exists
        if ($this->emailPreparations()->exists()) {
            return;
        }

        // Get the assigned user's email as the default recipient
        $defaultToEmail = $this->assignee ? $this->assignee->email : '';

        // Create default email content
        $defaultSubject = "Task Completion Confirmation - {$this->title}";
        $defaultBody = "Dear {$this->assignee->name},\n\n" .
                      "This is to confirm that the task '{$this->title}' has been completed successfully.\n\n" .
                      "Task Details:\n" .
                      "- Project: {$this->project->name}\n" .
                      "- Priority: " . ucfirst($this->priority) . "\n" .
                      "- Due Date: " . ($this->due_date ? $this->due_date->format('M d, Y') : 'Not set') . "\n\n" .
                      "Thank you for your hard work!\n\n" .
                      "Best regards,\n" .
                      "Task Management System";

        // Create the email preparation
        $this->emailPreparations()->create([
            'prepared_by' => auth()->id() ?? 1, // Fallback for system operations
            'to_emails' => $defaultToEmail,
            'cc_emails' => '',
            'bcc_emails' => '',
            'subject' => $defaultSubject,
            'body' => $defaultBody,
            'attachments' => [],
            'status' => 'draft',
        ]);
    }

    /**
     * Send approval email via Gmail OAuth
     */
    private function sendApprovalEmailViaGmail(User $approver)
    {
        try {
            $gmailOAuthService = app(\App\Services\GmailOAuthService::class);

            // Prepare email data for Gmail API
            $emailData = [
                'from' => $approver->email,
                'to' => [$this->assignee->email],
                'subject' => 'Task Approved: ' . $this->title,
                'body' => view('emails.task-approval-internal', [
                    'task' => $this,
                    'user' => $this->assignee,
                    'approver' => $approver,
                ])->render(),
            ];

            $success = $gmailOAuthService->sendEmail($approver, $emailData);

            if (!$success) {
                \Log::error('Gmail OAuth failed for approval email - Email not sent');
                throw new \Exception('Failed to send approval email via Gmail OAuth. Please check your Gmail connection.');
            }

        } catch (\Exception $e) {
            \Log::error('Gmail OAuth error for approval email: ' . $e->getMessage());
            throw $e;
        }
    }
}


