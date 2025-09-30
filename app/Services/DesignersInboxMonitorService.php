<?php

namespace App\Services;

use App\Models\Email;
use App\Models\EmailNotification;
use App\Models\User;
use App\Notifications\EmailReplyNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DesignersInboxMonitorService
{
    protected $imapHost;
    protected $imapPort;
    protected $imapUsername;
    protected $imapPassword;
    protected $imapFolder;

    public function __construct()
    {
        // Configure IMAP settings for designers@orion-contracting.com
        $this->imapHost = config('mail.imap.host', 'mail.orion-contracting.com');
        $this->imapPort = config('mail.imap.port', 993);
        $this->imapUsername = config('mail.imap.username', 'designers@orion-contracting.com');
        $this->imapPassword = config('mail.imap.password', '');
        $this->imapFolder = config('mail.imap.folder', 'INBOX');
    }

    /**
     * Monitor designers@orion-contracting.com inbox for new emails
     */
    public function monitorInbox(): array
    {
        try {
            Log::info('Starting designers inbox monitoring...');

            // Connect to IMAP
            $connection = $this->connectToImap();
            if (!$connection) {
                return ['success' => false, 'message' => 'Failed to connect to IMAP'];
            }

            // Get recent emails
            $emails = $this->getRecentEmails($connection);

            // Process each email
            $processedEmails = [];
            foreach ($emails as $emailData) {
                $result = $this->processIncomingEmail($emailData);
                if ($result) {
                    $processedEmails[] = $result;
                }
            }

            // Close connection
            imap_close($connection);

            Log::info('Designers inbox monitoring completed', [
                'emails_processed' => count($processedEmails)
            ]);

            return [
                'success' => true,
                'emails_processed' => count($processedEmails),
                'emails' => $processedEmails
            ];

        } catch (\Exception $e) {
            Log::error('Error monitoring designers inbox: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
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

            Log::info('IMAP connection successful to designers@orion-contracting.com');
            return $connection;

        } catch (\Exception $e) {
            Log::error('IMAP connection error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent emails from IMAP
     */
    protected function getRecentEmails($connection): array
    {
        try {
            $emails = [];

            // Get message count
            $messageCount = imap_num_msg($connection);
            Log::info("Found {$messageCount} messages in designers inbox");

            // Get recent messages (last 10)
            $start = max(1, $messageCount - 9);

            for ($i = $start; $i <= $messageCount; $i++) {
                $emailData = $this->parseEmailMessage($connection, $i);
                if ($emailData) {
                    $emails[] = $emailData;
                }
            }

            return $emails;

        } catch (\Exception $e) {
            Log::error('Error getting recent emails: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Parse email message from IMAP
     */
    protected function parseEmailMessage($connection, $messageNumber): ?array
    {
        try {
            $header = imap_headerinfo($connection, $messageNumber);
            $body = imap_body($connection, $messageNumber);

            if (!$header) {
                return null;
            }

            return [
                'message_number' => $messageNumber,
                'message_id' => $header->message_id ?? 'msg-' . $messageNumber . '-' . time(),
                'from' => $this->extractEmailAddress($header->from[0]->mailbox . '@' . $header->from[0]->host),
                'to' => $this->extractEmailAddress($header->to[0]->mailbox . '@' . $header->to[0]->host),
                'subject' => $header->subject ?? 'No Subject',
                'date' => $header->date ?? now()->toISOString(),
                'body' => $body ?? '',
                'is_reply' => $this->isReplyEmail($header->subject ?? ''),
            ];

        } catch (\Exception $e) {
            Log::error("Error parsing message {$messageNumber}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract email address from header
     */
    protected function extractEmailAddress($emailString): string
    {
        // Clean up email address
        $email = trim($emailString);
        $email = str_replace(['<', '>'], '', $email);
        return $email;
    }

    /**
     * Check if email is a reply
     */
    protected function isReplyEmail(string $subject): bool
    {
        return stripos($subject, 'Re:') === 0 ||
               stripos($subject, 'RE:') === 0 ||
               stripos($subject, 'Fwd:') === 0 ||
               stripos($subject, 'FWD:') === 0;
    }

    /**
     * Process incoming email and create notifications
     */
    protected function processIncomingEmail(array $emailData): ?array
    {
        try {
            // Check if this email is already processed
            $existingEmail = Email::where('message_id', $emailData['message_id'])->first();
            if ($existingEmail) {
                Log::info('Email already processed: ' . $emailData['message_id']);
                return null;
            }

            // Create email record
            $email = Email::create([
                'from_email' => $emailData['from'],
                'to_email' => $emailData['to'],
                'subject' => $emailData['subject'],
                'body' => $emailData['body'],
                'email_type' => 'received',
                'status' => 'received',
                'received_at' => now(),
                'message_id' => $emailData['message_id'],
                'is_tracked' => true,
            ]);

            // Try to find original email this is replying to
            $originalEmail = $this->findOriginalEmail($emailData);

            if ($originalEmail) {
                // This is a reply to an existing email
                $email->update([
                    'reply_to_email_id' => $originalEmail->id,
                    'user_id' => $originalEmail->user_id,
                    'task_id' => $originalEmail->task_id,
                ]);

                // Mark original email as replied
                $originalEmail->update(['replied_at' => now()]);

                // Create notification
                $this->createReplyNotification($originalEmail, $email);

                Log::info('Reply processed for email ID: ' . $originalEmail->id);

                return [
                    'type' => 'reply',
                    'original_email_id' => $originalEmail->id,
                    'reply_email_id' => $email->id,
                    'from' => $emailData['from'],
                    'subject' => $emailData['subject']
                ];
            } else {
                // This is a new email (not a reply)
                Log::info('New email received: ' . $emailData['subject']);

                return [
                    'type' => 'new_email',
                    'email_id' => $email->id,
                    'from' => $emailData['from'],
                    'subject' => $emailData['subject']
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error processing incoming email: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Find original email this is replying to
     */
    protected function findOriginalEmail(array $emailData): ?Email
    {
        try {
            // Try to find by subject (remove "Re:" prefix)
            $originalSubject = preg_replace('/^(Re:|RE:|Fwd:|FWD:)\s*/i', '', $emailData['subject']);

            $email = Email::where('email_type', 'sent')
                ->where('is_tracked', true)
                ->where('subject', 'LIKE', '%' . $originalSubject . '%')
                ->where('sent_at', '>=', now()->subDays(30))
                ->orderBy('sent_at', 'desc')
                ->first();

            return $email;

        } catch (\Exception $e) {
            Log::error('Error finding original email: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create notification for email reply
     */
    protected function createReplyNotification(Email $originalEmail, Email $replyEmail): void
    {
        try {
            $user = User::find($originalEmail->user_id);
            if (!$user) {
                Log::warning('User not found for email ID: ' . $originalEmail->id);
                return;
            }

            // Create database notification
            $notification = EmailNotification::create([
                'user_id' => $user->id,
                'email_id' => $originalEmail->id,
                'notification_type' => 'reply_received',
                'message' => "You received a reply from {$replyEmail->from_email} regarding: {$originalEmail->subject}",
                'is_read' => false,
            ]);

            // Send Laravel notification
            $user->notify(new EmailReplyNotification($originalEmail, $replyEmail));

            Log::info('Reply notification created for user: ' . $user->id);

        } catch (\Exception $e) {
            Log::error('Error creating reply notification: ' . $e->getMessage());
        }
    }

    /**
     * Test IMAP connection
     */
    public function testConnection(): array
    {
        try {
            $connection = $this->connectToImap();
            if ($connection) {
                $messageCount = imap_num_msg($connection);
                imap_close($connection);

                return [
                    'success' => true,
                    'message' => "Connected successfully. Found {$messageCount} messages.",
                    'message_count' => $messageCount
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to connect to IMAP server'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
