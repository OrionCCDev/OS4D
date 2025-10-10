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

            // Prepare attachments for Gmail OAuth service
            if ($this->emailPreparation->attachments && is_array($this->emailPreparation->attachments)) {
                Log::info('Job: Processing attachments for email - Count: ' . count($this->emailPreparation->attachments));
                $emailData['attachments'] = [];
                foreach ($this->emailPreparation->attachments as $attachmentPath) {
                    $fullPath = storage_path('app/' . $attachmentPath);
                    Log::info('Job: Checking attachment: ' . $fullPath . ' - Exists: ' . (file_exists($fullPath) ? 'Yes' : 'No'));
                    if (file_exists($fullPath)) {
                        $fileSize = filesize($fullPath);
                        $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';
                        Log::info('Job: Adding attachment: ' . basename($attachmentPath) . ' - Size: ' . $fileSize . ' bytes - MIME: ' . $mimeType);
                        $emailData['attachments'][] = [
                            'filename' => basename($attachmentPath),
                            'mime_type' => $mimeType,
                            'content' => file_get_contents($fullPath)
                        ];
                    } else {
                        Log::error('Job: Attachment file not found: ' . $fullPath);
                    }
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

                // Notify managers about the sent confirmation email
                // This method is in TaskController, need to refactor or pass necessary data
                // For now, we'll log it.
                Log::info('Job: Managers should be notified about confirmation email sent for task: ' . $this->task->id);
                // You might want to dispatch another job or event here to notify managers
                // Example: event(new ConfirmationEmailSent($this->task, $this->user));
            }

        } catch (\Exception $e) {
            Log::error('Background email sending job failed for task: ' . $this->task->id . ' - ' . $e->getMessage());
            // Optionally, update email preparation status to 'failed'
            $this->emailPreparation->update(['status' => 'failed']);
        }
    }
}
