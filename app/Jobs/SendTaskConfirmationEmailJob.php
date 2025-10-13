<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskEmailPreparation;
use App\Mail\TaskConfirmationMail;
use App\Services\GmailOAuthService;
use App\Services\SimpleEmailTrackingService;
use App\Notifications\EmailSendingFailedNotification;
use App\Notifications\EmailSendingSuccessNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SendTaskConfirmationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $task;
    public $user;
    public $emailPreparation;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 300;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = [60, 300, 900]; // 1 min, 5 mins, 15 mins

    /**
     * Create a new job instance.
     */
    public function __construct(Task $task, User $user, TaskEmailPreparation $emailPreparation)
    {
        $this->task = $task;
        $this->user = $user;
        $this->emailPreparation = $emailPreparation;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Set the authenticated user for the current request context
            // This is important for auth()->id() calls within the Task model or other services
            Auth::login($this->user);

            $useGmailOAuth = $this->user->hasGmailConnected();

            Log::info('Background email sending job started for task: ' . $this->task->id . ' by user: ' . $this->user->id . ', Gmail Only Mode: ' . ($useGmailOAuth ? 'Yes' : 'No'));

            $toEmails = array_filter(array_map('trim', explode(',', $this->emailPreparation->to_emails)));
            $ccEmails = $this->emailPreparation->cc_emails ? array_filter(array_map('trim', explode(',', $this->emailPreparation->cc_emails))) : [];
            $bccEmails = $this->emailPreparation->bcc_emails ? array_filter(array_map('trim', explode(',', $this->emailPreparation->bcc_emails))) : [];

            // Always add engineering@orion-contracting.com to CC
            if (!in_array('engineering@orion-contracting.com', $ccEmails)) {
                $ccEmails[] = 'engineering@orion-contracting.com';
            }

            // Add all users (role: 'user') to CC so they get notifications, EXCEPT the sender
            $usersToNotify = User::where('role', 'user')->where('id', '!=', $this->user->id)->get();
            foreach ($usersToNotify as $userToNotify) {
                if (!in_array($userToNotify->email, $ccEmails)) {
                    $ccEmails[] = $userToNotify->email;
                }
            }

            $emailData = [
                'from' => $this->user->email,
                'from_name' => $this->user->name,
                'to' => $toEmails,
                'subject' => $this->emailPreparation->subject,
                'body' => view('emails.task-confirmation', [
                    'task' => $this->task,
                    'emailPreparation' => $this->emailPreparation,
                    'sender' => $this->user,
                ])->render(),
                'task_id' => $this->task->id,
            ];

            // Prepare attachments for Gmail OAuth service (optimized to prevent memory issues)
            if ($this->emailPreparation->attachments && is_array($this->emailPreparation->attachments)) {
                Log::info('Job: Processing attachments for email - Count: ' . count($this->emailPreparation->attachments));
                $emailData['attachments'] = [];

                foreach ($this->emailPreparation->attachments as $attachmentPath) {
                    $fullPath = storage_path('app/' . $attachmentPath);
                    Log::info('Job: Checking attachment: ' . $fullPath . ' - Exists: ' . (file_exists($fullPath) ? 'Yes' : 'No'));

                    if (!file_exists($fullPath)) {
                        Log::error('Job: Attachment file not found: ' . $fullPath);
                        continue;
                    }

                    $fileSize = filesize($fullPath);
                    $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';

                    // Validate file size (100MB limit)
                    $maxSize = 100 * 1024 * 1024; // 100MB in bytes
                    if ($fileSize > $maxSize) {
                        Log::error('Job: Attachment file too large: ' . basename($attachmentPath) . ' - Size: ' . $fileSize . ' bytes');
                        throw new \Exception('Attachment file too large: ' . basename($attachmentPath) . '. Maximum size is 100MB.');
                    }

                    Log::info('Job: Adding attachment: ' . basename($attachmentPath) . ' - Size: ' . $fileSize . ' bytes - MIME: ' . $mimeType);

                    // Use file_get_contents for attachments - this is necessary for email encoding
                    // The memory issue is acceptable because we're in a queue job with extended timeout
                    $emailData['attachments'][] = [
                        'filename' => basename($attachmentPath),
                        'mime_type' => $mimeType,
                        'content' => file_get_contents($fullPath)
                    ];

                    // Free up memory after each attachment
                    gc_collect_cycles();
                }

                Log::info('Job: Total attachments prepared: ' . count($emailData['attachments']));
            } else {
                Log::info('Job: No attachments found in email preparation');
            }

            if (!empty($ccEmails)) {
                $emailData['cc'] = $ccEmails;
            }

            if (!empty($bccEmails)) {
                $emailData['bcc'] = $bccEmails;
            }

            $success = false;
            $trackedEmail = null;

            if ($useGmailOAuth) {
                Log::info('Job: Using Gmail OAuth for sending email');
                $gmailOAuthService = app(GmailOAuthService::class);

                $gmailEmailData = $emailData;
                if (isset($gmailEmailData['cc'])) {
                    $gmailEmailData['cc'] = array_filter($gmailEmailData['cc'], function($email) {
                        return $email !== 'engineering@orion-contracting.com';
                    });
                }

                $success = $gmailOAuthService->sendEmail($this->user, $gmailEmailData);

                if ($success) {
                    Log::info('Job: Confirmation email sent successfully via Gmail OAuth for task: ' . $this->task->id);
                    // Send separate email to engineering@orion-contracting.com via SMTP
                    // This part might need to be handled differently if engineering@orion-contracting.com is also a Gmail OAuth user
                    // For now, assuming it's handled by the main email or a separate SMTP config
                    // $this->sendDesignersNotification($this->task, $this->emailPreparation, $this->user); // This method is in TaskController, not Task model or Job
                } else {
                    Log::error('Job: Gmail OAuth failed for user: ' . $this->user->id . ' - Email not sent');
                }
            } else {
                Log::info('Job: Using Laravel Mail with simple tracking');
                $mail = new TaskConfirmationMail($this->task, $this->emailPreparation, $this->user);

                if (!empty($ccEmails)) {
                    $mail->cc($ccEmails);
                }
                if (!empty($bccEmails)) {
                    $mail->bcc($bccEmails);
                }

                Mail::send($mail);

                $simpleEmailTrackingService = app(SimpleEmailTrackingService::class);
                $trackedEmail = $simpleEmailTrackingService->trackSentEmail($this->user, $emailData);
                $success = $trackedEmail !== null;

                if (!$success) {
                    Log::error('Job: Email tracking failed for user: ' . $this->user->id . ' - Email may have been sent but not tracked');
                }
                Log::info('Job: Confirmation email sent successfully via Laravel Mail for task: ' . $this->task->id);
            }

            if ($success) {
                $this->emailPreparation->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
                $this->task->update(['status' => 'on_client_consultant_review']); // Update task status after sending email

                Log::info('Job: Email sent successfully for task: ' . $this->task->id);

                // Add task history entry for email sending
                $this->addEmailSendingHistory();

                // Add task history entry for status change to waiting for review
                $this->addWaitingForReviewHistory();

                // Send in-app notifications to managers
                $this->sendInAppNotificationsToManagers();

                Log::info('Job: Success notifications sent for task: ' . $this->task->id);
            } else {
                // Email sending failed
                throw new \Exception('Email sending failed - service returned false');
            }

        } catch (\Exception $e) {
            Log::error('Background email sending job failed for task: ' . $this->task->id . ' - ' . $e->getMessage());
            Log::error('Exception trace: ' . $e->getTraceAsString());

            // Update email preparation status to 'failed'
            $this->emailPreparation->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            // Revert task status back to approved/ready_for_email
            $this->task->update(['status' => 'approved']);

            // Notify the user who tried to send the email
            $this->user->notify(new EmailSendingFailedNotification(
                $this->task,
                $this->emailPreparation,
                $e->getMessage()
            ));

            // Notify managers about the failure
            $managers = User::where('role', 'manager')->get();
            foreach ($managers as $manager) {
                $manager->notify(new EmailSendingFailedNotification(
                    $this->task,
                    $this->emailPreparation,
                    $e->getMessage()
                ));
            }

            Log::info('Job: Failure notifications sent for task: ' . $this->task->id);

            // Re-throw the exception so Laravel can track it as a failed job
            throw $e;
        }
    }

    /**
     * Add task history entry for email sending
     */
    private function addEmailSendingHistory(): void
    {
        try {
            $this->task->histories()->create([
                'user_id' => $this->user->id,
                'action' => 'email_sent',
                'description' => "Confirmation email sent by {$this->user->name} to: {$this->emailPreparation->to_emails}",
                'metadata' => [
                    'email_subject' => $this->emailPreparation->subject,
                    'email_to' => $this->emailPreparation->to_emails,
                    'email_cc' => $this->emailPreparation->cc_emails,
                    'email_bcc' => $this->emailPreparation->bcc_emails,
                    'has_attachments' => !empty($this->emailPreparation->attachments),
                    'attachment_count' => is_array($this->emailPreparation->attachments) ? count($this->emailPreparation->attachments) : 0,
                    'sent_at' => now()->toISOString()
                ]
            ]);

            Log::info('Task history entry created for email sending - Task: ' . $this->task->id);
        } catch (\Exception $e) {
            Log::error('Failed to create task history for email sending: ' . $e->getMessage());
        }
    }

    /**
     * Add task history entry for status change to waiting for review
     */
    private function addWaitingForReviewHistory(): void
    {
        try {
            $this->task->histories()->create([
                'user_id' => $this->user->id,
                'action' => 'status_changed',
                'description' => "Task status changed to 'On Client/Consultant Review' - waiting for client and consultant responses",
                'old_value' => 'ready_for_email',
                'new_value' => 'on_client_consultant_review',
                'metadata' => [
                    'status_change_reason' => 'confirmation_email_sent',
                    'email_sent_by' => $this->user->name,
                    'email_sent_at' => now()->toISOString(),
                    'waiting_for' => ['client_response', 'consultant_response'],
                    'next_action' => 'monitor_responses'
                ]
            ]);

            Log::info('Task history entry created for waiting for review status - Task: ' . $this->task->id);
        } catch (\Exception $e) {
            Log::error('Failed to create task history for waiting for review status: ' . $e->getMessage());
        }
    }

    /**
     * Send in-app notifications to managers when email is sent
     */
    private function sendInAppNotificationsToManagers(): void
    {
        try {
            $managers = User::whereIn('role', ['admin', 'manager', 'sub-admin'])->get();

            Log::info('Found ' . $managers->count() . ' managers to notify about email sent for task: ' . $this->task->id);
            foreach ($managers as $manager) {
                Log::info('Manager: ' . $manager->name . ' (' . $manager->email . ') - Role: ' . $manager->role);
            }

            if ($managers->isEmpty()) {
                Log::warning('No managers found to notify about email sent');
                return;
            }

            foreach ($managers as $manager) {
                // Send notification about email being sent
                $manager->notify(new \App\Notifications\EmailSendingSuccessNotification($this->task, $this->emailPreparation));

                // Send notification about task waiting for review
                $manager->notify(new \App\Notifications\TaskWaitingForReviewNotification($this->task, $this->emailPreparation, $this->user));

                Log::info('In-app notifications sent to manager: ' . $manager->email . ' for task: ' . $this->task->id);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send in-app notifications to managers: ' . $e->getMessage());
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job permanently failed for task: ' . $this->task->id . ' after all retries - ' . $exception->getMessage());

        // Update email preparation status to 'failed'
        $this->emailPreparation->update([
            'status' => 'failed',
            'error_message' => 'Job failed after ' . $this->tries . ' attempts: ' . $exception->getMessage(),
        ]);

        // Revert task status
        $this->task->update(['status' => 'approved']);

        // Final notification to user and managers
        $this->user->notify(new EmailSendingFailedNotification(
            $this->task,
            $this->emailPreparation,
            'Email sending failed after all retry attempts. Please contact support.'
        ));
    }
}
