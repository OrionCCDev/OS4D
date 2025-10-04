<?php

namespace App\Services;

use App\Models\Email;
use App\Models\EmailNotification;
use App\Models\User;
use App\Models\Task;
use App\Notifications\EmailReplyNotification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class SimpleEmailTrackingService
{
    /**
     * Track sent email and store in database
     */
    public function trackSentEmail(User $user, array $emailData, string $messageId = null): ?Email
    {
        try {
            // Always add designers@orion-contracting.com to CC
            $ccEmails = $emailData['cc'] ?? [];
            if (!in_array('designers@orion-contracting.com', $ccEmails)) {
                $ccEmails[] = 'designers@orion-contracting.com';
            }

            // Create email record
            $email = Email::create([
                'from_email' => $emailData['from'],
                'to_email' => is_array($emailData['to']) ? implode(', ', $emailData['to']) : $emailData['to'],
                'subject' => $emailData['subject'],
                'body' => $emailData['body'],
                'email_type' => 'sent',
                'status' => 'sent',
                'sent_at' => now(),
                'message_id' => $messageId,
                'cc_emails' => $ccEmails,
                'bcc_emails' => $emailData['bcc'] ?? [],
                'is_tracked' => true,
                'user_id' => $user->id,
                'task_id' => $emailData['task_id'] ?? null,
            ]);

            Log::info('Email tracked successfully - ID: ' . $email->id . ', Message ID: ' . $messageId);

            // Create notification for managers about the sent email
            $this->createSentEmailNotification($email);

            return $email;
        } catch (\Exception $e) {
            Log::error('Failed to track email: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check for replies by monitoring designers@orion-contracting.com inbox
     * This would typically be called via a webhook or scheduled job
     */
    public function checkForReplies(): array
    {
        try {
            // Get all tracked emails that haven't been replied to
            $trackedEmails = Email::where('email_type', 'sent')
                ->where('is_tracked', true)
                ->whereNull('replied_at')
                ->where('sent_at', '>=', now()->subDays(30)) // Only check emails from last 30 days
                ->get();

            $replies = [];

            foreach ($trackedEmails as $email) {
                // Check if there's a reply in the designers@orion-contracting.com inbox
                $reply = $this->checkDesignersInboxForReply($email);
                if ($reply) {
                    $replies[] = $reply;
                }
            }

            return ['success' => true, 'replies' => $replies];
        } catch (\Exception $e) {
            Log::error('Error checking for replies: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Check designers@orion-contracting.com inbox for replies to specific email
     * This method can be called manually or via webhook when a reply is detected
     */
    protected function checkDesignersInboxForReply(Email $email): ?array
    {
        try {
            // For now, we'll implement a simple approach that can be triggered manually
            // In production, this would be called by:
            // 1. Email service webhooks (SendGrid, Mailgun, etc.)
            // 2. IMAP connection to designers@orion-contracting.com
            // 3. Email forwarding rules
            // 4. Manual webhook calls

            // Check if there are any recent replies in the database that match this email
            $recentReplies = Email::where('email_type', 'received')
                ->where('reply_to_email_id', $email->id)
                ->where('received_at', '>=', now()->subHours(24))
                ->first();

            if ($recentReplies) {
                Log::info('Found recent reply for email ID: ' . $email->id);
                return [
                    'reply_email' => $recentReplies,
                    'original_email' => $email,
                    'from' => $recentReplies->from_email,
                    'subject' => $recentReplies->subject,
                    'body' => $recentReplies->body,
                ];
            }

            return null; // No reply found
        } catch (\Exception $e) {
            Log::error('Error checking designers inbox: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Process a reply found in designers@orion-contracting.com inbox
     */
    public function processReply(array $replyData, Email $originalEmail): ?Email
    {
        try {
            // Create reply email record
            $replyEmail = Email::create([
                'from_email' => $replyData['from'],
                'to_email' => $originalEmail->from_email,
                'subject' => $replyData['subject'],
                'body' => $replyData['body'],
                'email_type' => 'received',
                'status' => 'received',
                'received_at' => now(),
                'message_id' => $replyData['message_id'] ?? null,
                'reply_to_email_id' => $originalEmail->id,
                'user_id' => $originalEmail->user_id,
                'task_id' => $originalEmail->task_id,
            ]);

            // Update original email
            $originalEmail->update(['replied_at' => now()]);

            // Create notification with reply email included
            $replyData['reply_email'] = $replyEmail;
            $this->createReplyNotification($originalEmail, $replyData);

            Log::info('Reply processed successfully for email ID: ' . $originalEmail->id);

            return $replyEmail;
        } catch (\Exception $e) {
            Log::error('Error processing reply: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create notification for email reply
     */
    protected function createReplyNotification(Email $originalEmail, array $replyData): void
    {
        try {
            $user = User::find($originalEmail->user_id);
            if (!$user) {
                Log::warning('User not found for email ID: ' . $originalEmail->id);
                return;
            }

            // Create notification for original sender
            $notification = EmailNotification::create([
                'user_id' => $user->id,
                'email_id' => $originalEmail->id,
                'notification_type' => 'reply_received',
                'message' => "You received a reply from {$replyData['from']} regarding: {$originalEmail->subject}",
                'is_read' => false,
            ]);

            // Send Laravel notification to original sender
            $task = $originalEmail->task_id ? Task::find($originalEmail->task_id) : null;
            if (isset($replyData['reply_email'])) {
                $user->notify(new EmailReplyNotification($originalEmail, $replyData['reply_email'], $task));
            }

            Log::info('Reply notification created for original sender: ' . $user->id);

            // ALSO create notification for manager (User ID 1)
            $manager = User::find(1);
            if ($manager && $manager->id !== $user->id) {
                $managerNotification = EmailNotification::create([
                    'user_id' => $manager->id,
                    'email_id' => $originalEmail->id,
                    'notification_type' => 'reply_received',
                    'message' => "Reply received from {$replyData['from']} for email: {$originalEmail->subject} (sent by {$user->name})",
                    'is_read' => false,
                ]);

                // Send Laravel notification to manager
                if (isset($replyData['reply_email'])) {
                    $manager->notify(new EmailReplyNotification($originalEmail, $replyData['reply_email'], $task));
                }

                Log::info('Reply notification created for manager: ' . $manager->id);
            }

        } catch (\Exception $e) {
            Log::error('Error creating reply notification: ' . $e->getMessage());
        }
    }

    /**
     * Create notification for sent email
     */
    protected function createSentEmailNotification(Email $email): void
    {
        try {
            $notificationService = app(NotificationService::class);

            // Get all managers and admins
            $managers = User::whereIn('role', ['admin', 'manager'])->get();
            $sender = User::find($email->user_id);

            foreach ($managers as $manager) {
                // Don't send notification to the sender
                if ($manager->id === $email->user_id) {
                    continue;
                }

                // Check if notification already exists to prevent duplicates
                $existingNotification = \App\Models\UnifiedNotification::where('user_id', $manager->id)
                    ->where('email_id', $email->id)
                    ->where('type', 'email_sent')
                    ->first();

                if ($existingNotification) {
                    Log::info("UnifiedNotification already exists for sent email ID: {$email->id}, user ID: {$manager->id}");
                    continue;
                }

                $notificationService->createEmailNotification(
                    $manager->id,
                    'email_sent',
                    'Email Sent',
                    "Email sent by {$sender->name} to: {$email->to_email}",
                    [
                        'from' => $email->from_email,
                        'to' => $email->to_email,
                        'subject' => $email->subject,
                        'sender_name' => $sender->name,
                        'has_attachments' => !empty($email->attachments),
                        'task_id' => $email->task_id
                    ],
                    $email->id,
                    'normal'
                );

                Log::info("Created UnifiedNotification for sent email: {$email->subject} for user: {$manager->id}");
            }

        } catch (\Exception $e) {
            Log::error('Error creating sent email notification: ' . $e->getMessage());
        }
    }

    /**
     * Handle incoming email webhook from designers@orion-contracting.com
     * This would be called when a reply is received
     */
    public function handleIncomingReply(array $emailData): bool
    {
        try {
            Log::info('Processing incoming reply: ' . $emailData['subject']);

            // Extract original email reference from subject or body
            $originalEmail = $this->findOriginalEmail($emailData);

            if (!$originalEmail) {
                Log::warning('Could not find original email for reply: ' . $emailData['subject']);
                Log::info('Available emails in database: ' . Email::where('email_type', 'sent')->count());
                return false;
            }

            Log::info('Found original email ID: ' . $originalEmail->id);

            // Process the reply
            $replyEmail = $this->processReply($emailData, $originalEmail);

            return $replyEmail !== null;
        } catch (\Exception $e) {
            Log::error('Error handling incoming reply: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Find original email based on reply data
     */
    protected function findOriginalEmail(array $replyData): ?Email
    {
        // Try to find by subject (remove "Re:" prefix)
        $originalSubject = preg_replace('/^Re:\s*/i', '', $replyData['subject']);

        $email = Email::where('email_type', 'sent')
            ->where('is_tracked', true)
            ->where('subject', 'LIKE', '%' . $originalSubject . '%')
            ->where('sent_at', '>=', now()->subDays(30))
            ->orderBy('sent_at', 'desc')
            ->first();

        return $email;
    }

    /**
     * Get email statistics for a user
     */
    public function getEmailStats(User $user): array
    {
        $emails = Email::where('user_id', $user->id)
            ->where('email_type', 'sent')
            ->where('is_tracked', true)
            ->where('sent_at', '>=', now()->subDays(30))
            ->get();

        $totalEmails = $emails->count();
        $repliedEmails = $emails->where('replied_at', '!=', null)->count();
        $replyRate = $totalEmails > 0 ? ($repliedEmails / $totalEmails) * 100 : 0;

        return [
            'total_emails' => $totalEmails,
            'replied_emails' => $repliedEmails,
            'reply_rate' => round($replyRate, 1),
            'pending_replies' => $totalEmails - $repliedEmails,
        ];
    }
}
