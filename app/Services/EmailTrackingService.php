<?php

namespace App\Services;

use App\Models\Email;
use App\Models\EmailNotification;
use App\Models\User;
use App\Services\GmailOAuthService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Google\Service\Gmail;

class EmailTrackingService
{
    protected $gmailOAuthService;

    public function __construct()
    {
        // We'll resolve GmailOAuthService when needed to avoid circular dependency
    }

    /**
     * Track sent email and store in database
     */
    public function trackSentEmail(User $user, array $emailData, string $gmailMessageId, string $threadId = null): ?Email
    {
        try {
            // Always add designers@orion-contracting.com to CC
            $ccEmails = $emailData['cc'] ?? [];
            if (!in_array('designers@orion-contracting.com', $ccEmails)) {
                $ccEmails[] = 'designers@orion-contracting.com';
            }

            // Generate tracking pixel URL
            $trackingPixelUrl = $this->generateTrackingPixelUrl($gmailMessageId);

            // Create email record
            $email = Email::create([
                'from_email' => $emailData['from'],
                'to_email' => is_array($emailData['to']) ? implode(', ', $emailData['to']) : $emailData['to'],
                'subject' => $emailData['subject'],
                'body' => $emailData['body'],
                'email_type' => 'sent',
                'status' => 'sent',
                'sent_at' => now(),
                'gmail_message_id' => $gmailMessageId,
                'thread_id' => $threadId,
                'cc_emails' => $ccEmails,
                'bcc_emails' => $emailData['bcc'] ?? [],
                'tracking_pixel_url' => $trackingPixelUrl,
                'is_tracked' => true,
                'user_id' => $user->id,
                'task_id' => $emailData['task_id'] ?? null,
            ]);

            Log::info('Email tracked successfully - ID: ' . $email->id . ', Gmail Message ID: ' . $gmailMessageId);

            // Create notification for managers about the sent email
            $this->createSentEmailNotification($email);

            return $email;
        } catch (\Exception $e) {
            Log::error('Failed to track email: ' . $e->getMessage());
            // Return null instead of throwing exception to not break email sending
            return null;
        }
    }

    /**
     * Check for replies to tracked emails
     */
    public function checkForReplies(User $user): array
    {
        $gmailOAuthService = app(GmailOAuthService::class);
        $gmailService = $gmailOAuthService->getGmailService($user);
        if (!$gmailService) {
            return ['success' => false, 'message' => 'Gmail service not available'];
        }

        $replies = [];

        try {
            // Get all tracked emails that haven't been replied to
            $trackedEmails = Email::where('user_id', $user->id)
                ->where('email_type', 'sent')
                ->where('is_tracked', true)
                ->whereNull('replied_at')
                ->get();

            foreach ($trackedEmails as $email) {
                if ($email->thread_id) {
                    $reply = $this->checkThreadForReplies($gmailService, $email);
                    if ($reply) {
                        $replies[] = $reply;
                    }
                }
            }

            return ['success' => true, 'replies' => $replies];
        } catch (\Exception $e) {
            Log::error('Error checking for replies: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Check a specific thread for replies
     */
    protected function checkThreadForReplies(Gmail $gmailService, Email $email): ?array
    {
        try {
            $thread = $gmailService->users_threads->get('me', $email->thread_id);
            $messages = $thread->getMessages();

            if (count($messages) > 1) {
                // There are replies
                $latestMessage = end($messages);
                $messageId = $latestMessage->getId();

                // Check if this reply is already processed
                $existingReply = Email::where('gmail_message_id', $messageId)->first();
                if ($existingReply) {
                    return null; // Already processed
                }

                // Process the reply
                $replyData = $this->processReplyMessage($gmailService, $latestMessage, $email);

                if ($replyData) {
                    // Update original email
                    $email->update(['replied_at' => now()]);

                    // Create notification
                    $this->createReplyNotification($email, $replyData);

                    return $replyData;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error checking thread for replies: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Process a reply message
     */
    protected function processReplyMessage(Gmail $gmailService, $message, Email $originalEmail): ?array
    {
        try {
            $payload = $message->getPayload();
            $headers = $payload->getHeaders();

            $fromEmail = $this->getHeaderValue($headers, 'From');
            $subject = $this->getHeaderValue($headers, 'Subject');
            $body = $this->extractBody($payload);

            // Create reply email record
            $replyEmail = Email::create([
                'from_email' => $fromEmail,
                'to_email' => $originalEmail->from_email,
                'subject' => $subject,
                'body' => $body,
                'email_type' => 'received',
                'status' => 'received',
                'received_at' => now(),
                'gmail_message_id' => $message->getId(),
                'thread_id' => $originalEmail->thread_id,
                'reply_to_email_id' => $originalEmail->id,
                'user_id' => $originalEmail->user_id,
                'task_id' => $originalEmail->task_id,
            ]);

            return [
                'reply_email' => $replyEmail,
                'original_email' => $originalEmail,
                'from' => $fromEmail,
                'subject' => $subject,
                'body' => $body,
            ];
        } catch (\Exception $e) {
            Log::error('Error processing reply message: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create notification for email reply
     */
    protected function createReplyNotification(Email $originalEmail, array $replyData): void
    {
        EmailNotification::create([
            'user_id' => $originalEmail->user_id,
            'email_id' => $originalEmail->id,
            'notification_type' => 'reply_received',
            'message' => "You received a reply from {$replyData['from']} regarding: {$originalEmail->subject}",
            'is_read' => false,
        ]);

        Log::info('Reply notification created for email ID: ' . $originalEmail->id);
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
     * Generate tracking pixel URL
     */
    protected function generateTrackingPixelUrl(string $messageId): string
    {
        return url('/email/track/' . $messageId . '.png');
    }

    /**
     * Handle tracking pixel request
     */
    public function handleTrackingPixel(string $messageId): void
    {
        try {
            $email = Email::where('gmail_message_id', $messageId)->first();

            if ($email && !$email->opened_at) {
                $email->update(['opened_at' => now()]);

                // Create notification
                EmailNotification::create([
                    'user_id' => $email->user_id,
                    'email_id' => $email->id,
                    'notification_type' => 'email_opened',
                    'message' => "Your email '{$email->subject}' was opened",
                    'is_read' => false,
                ]);

                Log::info('Email opened - ID: ' . $email->id . ', Message ID: ' . $messageId);
            }
        } catch (\Exception $e) {
            Log::error('Failed to handle tracking pixel: ' . $e->getMessage());
            // Don't throw exception to avoid breaking the tracking pixel response
        }
    }

    /**
     * Get header value from Gmail headers
     */
    protected function getHeaderValue(array $headers, string $name): ?string
    {
        foreach ($headers as $header) {
            if ($header->getName() === $name) {
                return $header->getValue();
            }
        }
        return null;
    }

    /**
     * Extract body from Gmail message payload
     */
    protected function extractBody($payload): string
    {
        $body = '';

        if ($payload->getBody() && $payload->getBody()->getData()) {
            $body = base64_decode(str_replace(['-', '_'], ['+', '/'], $payload->getBody()->getData()));
        } elseif ($payload->getParts()) {
            foreach ($payload->getParts() as $part) {
                if ($part->getMimeType() === 'text/html' || $part->getMimeType() === 'text/plain') {
                    if ($part->getBody() && $part->getBody()->getData()) {
                        $body = base64_decode(str_replace(['-', '_'], ['+', '/'], $part->getBody()->getData()));
                        break;
                    }
                }
            }
        }

        return $body;
    }

    /**
     * Get email statistics for a user
     */
    public function getEmailStats(User $user): array
    {
        try {
            $sentEmails = Email::where('user_id', $user->id)
                ->where('email_type', 'sent')
                ->count();

            $openedEmails = Email::where('user_id', $user->id)
                ->where('email_type', 'sent')
                ->whereNotNull('opened_at')
                ->count();

            $repliedEmails = Email::where('user_id', $user->id)
                ->where('email_type', 'sent')
                ->whereNotNull('replied_at')
                ->count();

            return [
                'sent' => $sentEmails,
                'opened' => $openedEmails,
                'replied' => $repliedEmails,
                'open_rate' => $sentEmails > 0 ? round(($openedEmails / $sentEmails) * 100, 2) : 0,
                'reply_rate' => $sentEmails > 0 ? round(($repliedEmails / $sentEmails) * 100, 2) : 0,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get email stats: ' . $e->getMessage());
            return [
                'sent' => 0,
                'opened' => 0,
                'replied' => 0,
                'open_rate' => 0,
                'reply_rate' => 0,
            ];
        }
    }
}
