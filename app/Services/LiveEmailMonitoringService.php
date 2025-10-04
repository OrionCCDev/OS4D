<?php

namespace App\Services;

use App\Models\Email;
use App\Models\EmailNotification;
use App\Models\User;
use App\Models\Task;
use App\Notifications\EmailReplyNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class LiveEmailMonitoringService
{
    protected $imapHost;
    protected $imapPort;
    protected $imapUsername;
    protected $imapPassword;
    protected $imapFolder;

    public function __construct()
    {
        $this->imapHost = config('mail.imap.host', 'mail.orion-contracting.com');
        $this->imapPort = config('mail.imap.port', 993);
        $this->imapUsername = config('mail.imap.username', 'engineering@orion-contracting.com');
        $this->imapPassword = config('mail.imap.password', '');
        $this->imapFolder = config('mail.imap.folder', 'INBOX');
    }

    /**
     * Monitor designers inbox for new emails and replies
     */
    public function monitorInbox(): array
    {
        $results = [
            'new_emails' => 0,
            'replies' => 0,
            'notifications_created' => 0,
            'errors' => []
        ];

        try {
            // Connect to IMAP
            $connection = $this->connectToImap();
            if (!$connection) {
                $results['errors'][] = 'Failed to connect to IMAP server';
                return $results;
            }

            // Get recent emails (last 24 hours)
            $emails = $this->fetchRecentEmails($connection);

            foreach ($emails as $emailData) {
                $processed = $this->processIncomingEmail($emailData);

                if ($processed['type'] === 'reply') {
                    $results['replies']++;
                } else {
                    $results['new_emails']++;
                }

                if ($processed['notifications_created'] > 0) {
                    $results['notifications_created'] += $processed['notifications_created'];
                }
            }

            imap_close($connection);

        } catch (\Exception $e) {
            Log::error('Live email monitoring error: ' . $e->getMessage());
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Connect to IMAP server
     */
    protected function connectToImap()
    {
        try {
            $connectionString = "{{$this->imapHost}:{$this->imapPort}/imap/ssl}INBOX";
            $connection = imap_open($connectionString, $this->imapUsername, $this->imapPassword);

            if (!$connection) {
                Log::error('IMAP connection failed: ' . imap_last_error());
                return false;
            }

            return $connection;
        } catch (\Exception $e) {
            Log::error('IMAP connection error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch recent emails from IMAP
     */
    protected function fetchRecentEmails($connection): array
    {
        $emails = [];

        try {
            // Get emails from last 24 hours
            $searchCriteria = 'SINCE "' . date('d-M-Y', strtotime('-24 hours')) . '"';
            $emailNumbers = imap_search($connection, $searchCriteria);

            if (!$emailNumbers) {
                return $emails;
            }

            foreach ($emailNumbers as $emailNumber) {
                $header = imap_headerinfo($connection, $emailNumber);
                $body = imap_body($connection, $emailNumber);

                $emails[] = [
                    'message_id' => $header->message_id ?? uniqid(),
                    'from' => $this->extractEmailAddress($header->from[0]->mailbox . '@' . $header->from[0]->host),
                    'to' => $this->extractEmailAddress($header->to[0]->mailbox . '@' . $header->to[0]->host),
                    'cc' => isset($header->cc) ? $this->extractCcAddresses($header->cc) : [],
                    'subject' => $header->subject ?? '',
                    'body' => $body,
                    'date' => $header->date ?? now(),
                    'in_reply_to' => $header->in_reply_to ?? null,
                    'references' => $header->references ?? null,
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error fetching emails: ' . $e->getMessage());
        }

        return $emails;
    }

    /**
     * Process incoming email and create notifications
     */
    protected function processIncomingEmail(array $emailData): array
    {
        $result = [
            'type' => 'new_email',
            'notifications_created' => 0,
            'processed' => false
        ];

        try {
            // Check if this is a reply
            $isReply = $this->isReplyEmail($emailData);

            if ($isReply) {
                $result['type'] = 'reply';
                $this->processReplyEmail($emailData);
            } else {
                $this->processNewEmail($emailData);
            }

            $result['processed'] = true;
            $result['notifications_created'] = $this->createNotificationsForEmail($emailData, $isReply);

        } catch (\Exception $e) {
            Log::error('Error processing email: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Check if email is a reply
     */
    protected function isReplyEmail(array $emailData): bool
    {
        // Check if subject starts with "Re:" or "RE:"
        if (preg_match('/^(Re:|RE:)/i', $emailData['subject'])) {
            return true;
        }

        // Check if has in_reply_to or references
        if (!empty($emailData['in_reply_to']) || !empty($emailData['references'])) {
            return true;
        }

        return false;
    }

    /**
     * Process reply email
     */
    protected function processReplyEmail(array $emailData): void
    {
        // Find original email
        $originalEmail = $this->findOriginalEmail($emailData);

        if ($originalEmail) {
            // Create reply email record
            $replyEmail = Email::create([
                'user_id' => $originalEmail->user_id,
                'from_email' => $emailData['from'],
                'to_email' => $emailData['to'],
                'subject' => $emailData['subject'],
                'body' => $emailData['body'],
                'email_type' => 'received',
                'status' => 'received',
                'is_tracked' => false,
                'received_at' => now(),
                'reply_to_email_id' => $originalEmail->id,
                'thread_id' => $originalEmail->thread_id,
                'gmail_message_id' => $emailData['message_id'],
            ]);

            Log::info('Reply email processed: ' . $replyEmail->id);
        }
    }

    /**
     * Process new email
     */
    protected function processNewEmail(array $emailData): void
    {
        // Create new email record
        $email = Email::create([
            'user_id' => 1, // Default to manager
            'from_email' => $emailData['from'],
            'to_email' => $emailData['to'],
            'subject' => $emailData['subject'],
            'body' => $emailData['body'],
            'email_type' => 'received',
            'status' => 'received',
            'is_tracked' => false,
            'received_at' => now(),
            'gmail_message_id' => $emailData['message_id'],
        ]);

        Log::info('New email processed: ' . $email->id);
    }

    /**
     * Find original email for reply
     */
    protected function findOriginalEmail(array $emailData): ?Email
    {
        // Try to find by message ID references
        if (!empty($emailData['in_reply_to'])) {
            $originalEmail = Email::where('gmail_message_id', $emailData['in_reply_to'])->first();
            if ($originalEmail) {
                return $originalEmail;
            }
        }

        // Try to find by subject (remove Re: prefix)
        $cleanSubject = preg_replace('/^(Re:|RE:)\s*/i', '', $emailData['subject']);
        $originalEmail = Email::where('subject', $cleanSubject)
                             ->where('email_type', 'sent')
                             ->orderBy('created_at', 'desc')
                             ->first();

        return $originalEmail;
    }

    /**
     * Create notifications for email
     */
    protected function createNotificationsForEmail(array $emailData, bool $isReply): int
    {
        $notificationsCreated = 0;

        try {
            // Get all users who should be notified
            $usersToNotify = $this->getUsersToNotify($emailData, $isReply);

            foreach ($usersToNotify as $user) {
                $this->createNotificationForUser($user, $emailData, $isReply);
                $notificationsCreated++;
            }

        } catch (\Exception $e) {
            Log::error('Error creating notifications: ' . $e->getMessage());
        }

        return $notificationsCreated;
    }

    /**
     * Get users who should be notified
     */
    protected function getUsersToNotify(array $emailData, bool $isReply): array
    {
        $users = [];

        if ($isReply) {
            // For replies, notify original sender and manager
            $originalEmail = $this->findOriginalEmail($emailData);
            if ($originalEmail) {
                $users[] = User::find($originalEmail->user_id);
            }

            // Always notify manager
            $manager = User::find(1);
            if ($manager) {
                $users[] = $manager;
            }
        } else {
            // For new emails, notify users based on TO and CC
            $allRecipients = array_merge([$emailData['to']], $emailData['cc']);

            foreach ($allRecipients as $recipient) {
                $user = User::where('email', $recipient)->first();
                if ($user) {
                    $users[] = $user;
                }
            }

            // Always notify manager for new emails
            $manager = User::find(1);
            if ($manager) {
                $users[] = $manager;
            }
        }

        // Remove duplicates and null values
        return array_filter(array_unique($users, SORT_REGULAR));
    }

    /**
     * Create notification for specific user
     */
    protected function createNotificationForUser(User $user, array $emailData, bool $isReply): void
    {
        try {
            $message = $isReply
                ? "You received a reply from {$emailData['from']} regarding: {$emailData['subject']}"
                : "New email received from {$emailData['from']}: {$emailData['subject']}";

            $notificationType = $isReply ? 'reply_received' : 'email_received';

            // Find or create email record
            $email = Email::where('gmail_message_id', $emailData['message_id'])->first();
            if (!$email) {
                $email = Email::create([
                    'user_id' => $user->id,
                    'from_email' => $emailData['from'],
                    'to_email' => $emailData['to'],
                    'subject' => $emailData['subject'],
                    'body' => $emailData['body'],
                    'email_type' => 'received',
                    'status' => 'received',
                    'is_tracked' => false,
                    'received_at' => now(),
                    'gmail_message_id' => $emailData['message_id'],
                ]);
            }

            // Create notification
            EmailNotification::create([
                'user_id' => $user->id,
                'email_id' => $email->id,
                'notification_type' => $notificationType,
                'message' => $message,
                'is_read' => false,
            ]);

            // Send Laravel notification
            $user->notify(new EmailReplyNotification($email, $email));

            Log::info("Notification created for user {$user->id}: {$message}");

        } catch (\Exception $e) {
            Log::error("Error creating notification for user {$user->id}: " . $e->getMessage());
        }
    }

    /**
     * Extract email address from header
     */
    protected function extractEmailAddress(string $address): string
    {
        return trim($address, '<>');
    }

    /**
     * Extract CC addresses from header
     */
    protected function extractCcAddresses(array $ccHeader): array
    {
        $addresses = [];
        foreach ($ccHeader as $cc) {
            $addresses[] = $this->extractEmailAddress($cc->mailbox . '@' . $cc->host);
        }
        return $addresses;
    }

    /**
     * Get live email statistics
     */
    public function getLiveStats(): array
    {
        return [
            'total_emails' => Email::count(),
            'replies_today' => Email::where('email_type', 'received')
                ->whereNotNull('reply_to_email_id')
                ->whereDate('created_at', today())
                ->count(),
            'new_emails_today' => Email::where('email_type', 'received')
                ->whereNull('reply_to_email_id')
                ->whereDate('created_at', today())
                ->count(),
            'unread_notifications' => EmailNotification::where('is_read', false)->count(),
            'last_monitoring' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
