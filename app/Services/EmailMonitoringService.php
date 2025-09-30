<?php

namespace App\Services;

use App\Models\Email;
use App\Models\User;
use App\Models\Task;
use App\Notifications\EmailReplyNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class EmailMonitoringService
{
    /**
     * Monitor emails for replies using multiple methods
     */
    public function monitorForReplies(): array
    {
        $results = [
            'webhook_replies' => 0,
            'scheduled_replies' => 0,
            'errors' => []
        ];

        try {
            // Method 1: Check via webhook (if configured)
            $webhookResult = $this->checkWebhookReplies();
            $results['webhook_replies'] = $webhookResult['count'];
            if ($webhookResult['error']) {
                $results['errors'][] = $webhookResult['error'];
            }

            // Method 2: Check via scheduled monitoring
            $scheduledResult = $this->checkScheduledReplies();
            $results['scheduled_replies'] = $scheduledResult['count'];
            if ($scheduledResult['error']) {
                $results['errors'][] = $scheduledResult['error'];
            }

            Log::info('Email monitoring completed', $results);

        } catch (\Exception $e) {
            Log::error('Email monitoring failed: ' . $e->getMessage());
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Check for replies via webhook (if email service provider is configured)
     */
    protected function checkWebhookReplies(): array
    {
        try {
            // This would typically be called by your email service provider
            // (SendGrid, Mailgun, etc.) when they receive a reply
            
            Log::info('Webhook reply check - this should be called by email service provider');
            
            return ['count' => 0, 'error' => null];
        } catch (\Exception $e) {
            return ['count' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check for replies via scheduled monitoring
     */
    protected function checkScheduledReplies(): array
    {
        try {
            $simpleEmailService = app(SimpleEmailTrackingService::class);
            $result = $simpleEmailService->checkForReplies();
            
            if ($result['success']) {
                return ['count' => count($result['replies']), 'error' => null];
            } else {
                return ['count' => 0, 'error' => $result['message']];
            }
        } catch (\Exception $e) {
            return ['count' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process a detected reply and send notifications
     */
    public function processDetectedReply(array $replyData, Email $originalEmail): bool
    {
        try {
            $simpleEmailService = app(SimpleEmailTrackingService::class);
            $replyEmail = $simpleEmailService->processReply($replyData, $originalEmail);
            
            if ($replyEmail) {
                // Send additional notifications to relevant users
                $this->notifyRelevantUsers($originalEmail, $replyEmail);
                
                Log::info('Reply processed and notifications sent for email ID: ' . $originalEmail->id);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error('Error processing detected reply: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notify relevant users about the email reply
     */
    protected function notifyRelevantUsers(Email $originalEmail, Email $replyEmail): void
    {
        try {
            $user = User::find($originalEmail->user_id);
            $task = $originalEmail->task_id ? Task::find($originalEmail->task_id) : null;
            
            if ($user) {
                // Send notification to original sender
                $user->notify(new EmailReplyNotification($originalEmail, $replyEmail, $task));
                
                // If there's a related task, notify task stakeholders
                if ($task) {
                    $this->notifyTaskStakeholders($task, $originalEmail, $replyEmail);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error notifying users about reply: ' . $e->getMessage());
        }
    }

    /**
     * Notify task stakeholders about email reply
     */
    protected function notifyTaskStakeholders(Task $task, Email $originalEmail, Email $replyEmail): void
    {
        try {
            // Notify task assignee if different from original sender
            if ($task->assigned_to && $task->assigned_to !== $originalEmail->user_id) {
                $assignee = User::find($task->assigned_to);
                if ($assignee) {
                    $assignee->notify(new EmailReplyNotification($originalEmail, $replyEmail, $task));
                }
            }

            // Notify project manager if different from original sender
            if ($task->project && $task->project->manager_id && $task->project->manager_id !== $originalEmail->user_id) {
                $manager = User::find($task->project->manager_id);
                if ($manager) {
                    $manager->notify(new EmailReplyNotification($originalEmail, $replyEmail, $task));
                }
            }

            // Notify external stakeholders if configured
            $this->notifyExternalStakeholders($task, $originalEmail, $replyEmail);

        } catch (\Exception $e) {
            Log::error('Error notifying task stakeholders: ' . $e->getMessage());
        }
    }

    /**
     * Notify external stakeholders about email reply
     */
    protected function notifyExternalStakeholders(Task $task, Email $originalEmail, Email $replyEmail): void
    {
        try {
            // Get external stakeholders for this task/project
            $stakeholders = \App\Models\ExternalStakeholder::where('project_id', $task->project_id)
                ->orWhere('company_id', $task->project->company_id)
                ->get();

            foreach ($stakeholders as $stakeholder) {
                // Send email notification to external stakeholder
                Mail::to($stakeholder->email)
                    ->send(new \App\Mail\EmailReplyStakeholderNotification($originalEmail, $replyEmail, $task, $stakeholder));
            }

        } catch (\Exception $e) {
            Log::error('Error notifying external stakeholders: ' . $e->getMessage());
        }
    }

    /**
     * Get email monitoring statistics
     */
    public function getMonitoringStats(): array
    {
        try {
            $totalSentEmails = Email::where('email_type', 'sent')
                ->where('is_tracked', true)
                ->count();

            $totalReplies = Email::where('email_type', 'received')
                ->whereNotNull('reply_to_email_id')
                ->count();

            $repliedEmails = Email::where('email_type', 'sent')
                ->where('is_tracked', true)
                ->whereNotNull('replied_at')
                ->count();

            $pendingReplies = $totalSentEmails - $repliedEmails;

            return [
                'total_sent_emails' => $totalSentEmails,
                'total_replies' => $totalReplies,
                'replied_emails' => $repliedEmails,
                'pending_replies' => $pendingReplies,
                'reply_rate' => $totalSentEmails > 0 ? round(($repliedEmails / $totalSentEmails) * 100, 2) : 0,
                'last_check' => now()->format('Y-m-d H:i:s'),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting monitoring stats: ' . $e->getMessage());
            return [
                'total_sent_emails' => 0,
                'total_replies' => 0,
                'replied_emails' => 0,
                'pending_replies' => 0,
                'reply_rate' => 0,
                'last_check' => now()->format('Y-m-d H:i:s'),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Setup email monitoring for a specific email service provider
     */
    public function setupEmailProviderMonitoring(string $provider): array
    {
        $setupInstructions = [];

        switch (strtolower($provider)) {
            case 'sendgrid':
                $setupInstructions = [
                    'webhook_url' => url('/email/webhook/incoming'),
                    'events' => ['inbound'],
                    'instructions' => [
                        '1. Go to SendGrid Dashboard → Settings → Mail Settings → Inbound Parse',
                        '2. Add a new hostname (e.g., inbound.yourdomain.com)',
                        '3. Set the POST URL to: ' . url('/email/webhook/incoming'),
                        '4. Update your DNS MX record to point to mx.sendgrid.net',
                        '5. Configure email forwarding to designers@orion-contracting.com'
                    ]
                ];
                break;

            case 'mailgun':
                $setupInstructions = [
                    'webhook_url' => url('/email/webhook/incoming'),
                    'events' => ['message_received'],
                    'instructions' => [
                        '1. Go to Mailgun Dashboard → Webhooks',
                        '2. Add webhook for "message_received" event',
                        '3. Set webhook URL to: ' . url('/email/webhook/incoming'),
                        '4. Configure email routing to designers@orion-contracting.com'
                    ]
                ];
                break;

            case 'postmark':
                $setupInstructions = [
                    'webhook_url' => url('/email/webhook/incoming'),
                    'events' => ['inbound'],
                    'instructions' => [
                        '1. Go to Postmark Dashboard → Servers → Inbound',
                        '2. Configure inbound email processing',
                        '3. Set webhook URL to: ' . url('/email/webhook/incoming'),
                        '4. Configure email forwarding to designers@orion-contracting.com'
                    ]
                ];
                break;

            default:
                $setupInstructions = [
                    'error' => 'Provider not supported',
                    'supported_providers' => ['sendgrid', 'mailgun', 'postmark']
                ];
        }

        return $setupInstructions;
    }
}
