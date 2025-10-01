<?php

namespace App\Services;

use App\Models\Email;
use App\Models\User;
use App\Services\DesignersInboxNotificationService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DesignersInboxEmailService
{
    protected $imapHost;
    protected $imapPort;
    protected $imapUsername;
    protected $imapPassword;
    protected $imapFolder;
    protected $notificationService;

    public function __construct(DesignersInboxNotificationService $notificationService)
    {
        $this->imapHost = config('mail.imap.host', 'mail.orion-contracting.com');
        $this->imapPort = config('mail.imap.port', 993);
        $this->imapUsername = config('mail.imap.username', 'designers@orion-contracting.com');
        $this->imapPassword = config('mail.imap.password', '');
        $this->imapFolder = config('mail.imap.folder', 'INBOX');
        $this->notificationService = $notificationService;
    }

    /**
     * Fetch all emails from designers inbox
     */
    public function fetchAllEmails(int $maxResults = 100): array
    {
        $result = [
            'success' => false,
            'emails' => [],
            'total_fetched' => 0,
            'errors' => []
        ];

        try {
            $connection = $this->connectToImap();
            if (!$connection) {
                $result['errors'][] = 'Failed to connect to IMAP server';
                return $result;
            }

            // Get message count
            $messageCount = imap_num_msg($connection);
            Log::info("Found {$messageCount} messages in designers inbox");

            // Get recent messages (limit by maxResults)
            $start = max(1, $messageCount - $maxResults + 1);
            $emails = [];

            for ($i = $start; $i <= $messageCount; $i++) {
                $emailData = $this->parseEmailMessage($connection, $i);
                if ($emailData) {
                    $emails[] = $emailData;
                }
            }

            imap_close($connection);

            $result['success'] = true;
            $result['emails'] = $emails;
            $result['total_fetched'] = count($emails);

            Log::info('Successfully fetched ' . count($emails) . ' emails from designers inbox');

        } catch (\Exception $e) {
            Log::error('Error fetching emails from designers inbox: ' . $e->getMessage());
            $result['errors'][] = $e->getMessage();
        }

        return $result;
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

            // Extract email addresses
            $fromEmail = $this->extractEmailAddress($header->from[0]->mailbox . '@' . $header->from[0]->host);
            $toEmail = $this->extractEmailAddress($header->to[0]->mailbox . '@' . $header->to[0]->host);

            // Extract CC emails
            $ccEmails = [];
            if (isset($header->cc) && is_array($header->cc)) {
                foreach ($header->cc as $cc) {
                    $ccEmails[] = $this->extractEmailAddress($cc->mailbox . '@' . $cc->host);
                }
            }

            // Extract attachments
            $attachments = $this->extractAttachments($connection, $messageNumber);

            return [
                'message_number' => $messageNumber,
                'message_id' => $header->message_id ?? 'msg-' . $messageNumber . '-' . time(),
                'from_email' => $fromEmail,
                'to_email' => $toEmail,
                'cc_emails' => $ccEmails,
                'subject' => $header->subject ?? 'No Subject',
                'date' => $this->parseImapDate($header->date ?? ''),
                'body' => $this->cleanEmailBody($body ?? ''),
                'attachments' => $attachments,
                'in_reply_to' => $header->in_reply_to ?? null,
                'references' => $header->references ?? null,
                'is_reply' => $this->isReplyEmail($header->subject ?? ''),
                'size' => $header->Size ?? 0,
            ];

        } catch (\Exception $e) {
            Log::error("Error parsing message {$messageNumber}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract email address from header
     */
    protected function extractEmailAddress(string $emailString): string
    {
        // Remove any angle brackets and clean up
        $email = trim($emailString, '<>');

        // Validate email format
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }

        return $emailString;
    }

    /**
     * Parse IMAP date format
     */
    protected function parseImapDate(string $dateString): ?Carbon
    {
        try {
            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            Log::warning('Could not parse IMAP date: ' . $dateString);
            return null;
        }
    }

    /**
     * Clean email body
     */
    protected function cleanEmailBody(string $body): string
    {
        // Decode quoted-printable if needed
        if (strpos($body, 'quoted-printable') !== false) {
            $body = quoted_printable_decode($body);
        }

        // Remove excessive whitespace
        $body = preg_replace('/\s+/', ' ', $body);

        return trim($body);
    }

    /**
     * Extract attachments from email
     */
    protected function extractAttachments($connection, $messageNumber): array
    {
        $attachments = [];

        try {
            $structure = imap_fetchstructure($connection, $messageNumber);

            if (isset($structure->parts) && is_array($structure->parts)) {
                foreach ($structure->parts as $partNumber => $part) {
                    if (isset($part->dparameters) && is_array($part->dparameters)) {
                        foreach ($part->dparameters as $param) {
                            if (strtolower($param->attribute) === 'filename') {
                                $attachments[] = [
                                    'filename' => $param->value,
                                    'mime_type' => $part->type ?? 'application/octet-stream',
                                    'size' => $part->bytes ?? 0,
                                    'part_number' => $partNumber + 1,
                                ];
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Error extracting attachments from message {$messageNumber}: " . $e->getMessage());
        }

        return $attachments;
    }

    /**
     * Check if email is a reply
     */
    protected function isReplyEmail(string $subject): bool
    {
        return preg_match('/^(Re:|RE:)/i', $subject) ||
               preg_match('/^(Fwd:|FWD:)/i', $subject);
    }

    /**
     * Store fetched emails in database
     */
    public function storeEmailsInDatabase(array $emails, User $user): array
    {
        $result = [
            'stored' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        foreach ($emails as $emailData) {
            try {
                // Check if email already exists
                $existingEmail = Email::where('message_id', $emailData['message_id'])->first();

                if ($existingEmail) {
                    $result['skipped']++;
                    continue;
                }

                // Create new email record
                $email = Email::create([
                    'from_email' => $emailData['from_email'],
                    'to_email' => $emailData['to_email'],
                    'cc_emails' => $emailData['cc_emails'],
                    'subject' => $emailData['subject'],
                    'body' => $emailData['body'],
                    'received_at' => $emailData['date'] ?? now(),
                    'status' => 'received',
                    'message_id' => $emailData['message_id'],
                    'reply_to_email_id' => $emailData['in_reply_to'] ? $this->findReplyToEmail($emailData['in_reply_to']) : null,
                    'attachments' => $emailData['attachments'],
                    'user_id' => $user->id,
                    'email_type' => 'received',
                    'email_source' => 'designers_inbox', // Mark as from designers inbox
                ]);

                // Create notifications for managers
                $this->notificationService->processEmailNotifications($email);

                $result['stored']++;

            } catch (\Exception $e) {
                Log::error('Error storing designers inbox email: ' . $e->getMessage());
                $result['errors'][] = $e->getMessage();
            }
        }

        return $result;
    }

    /**
     * Find email that this is a reply to
     */
    protected function findReplyToEmail(string $inReplyTo): ?int
    {
        $email = Email::where('message_id', $inReplyTo)->first();
        return $email ? $email->id : null;
    }

    /**
     * Search emails by criteria
     */
    public function searchEmails(array $criteria = []): array
    {
        $result = [
            'success' => false,
            'emails' => [],
            'total_found' => 0,
            'errors' => []
        ];

        try {
            $connection = $this->connectToImap();
            if (!$connection) {
                $result['errors'][] = 'Failed to connect to IMAP server';
                return $result;
            }

            // Build search criteria
            $searchCriteria = $this->buildImapSearchCriteria($criteria);

            $emailNumbers = imap_search($connection, $searchCriteria);

            if (!$emailNumbers) {
                imap_close($connection);
                $result['success'] = true;
                return $result;
            }

            $emails = [];
            foreach ($emailNumbers as $emailNumber) {
                $emailData = $this->parseEmailMessage($connection, $emailNumber);
                if ($emailData) {
                    $emails[] = $emailData;
                }
            }

            imap_close($connection);

            $result['success'] = true;
            $result['emails'] = $emails;
            $result['total_found'] = count($emails);

        } catch (\Exception $e) {
            Log::error('Error searching designers inbox emails: ' . $e->getMessage());
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Build IMAP search criteria
     */
    protected function buildImapSearchCriteria(array $criteria): string
    {
        $searchParts = [];

        if (!empty($criteria['from'])) {
            $searchParts[] = 'FROM "' . $criteria['from'] . '"';
        }

        if (!empty($criteria['to'])) {
            $searchParts[] = 'TO "' . $criteria['to'] . '"';
        }

        if (!empty($criteria['subject'])) {
            $searchParts[] = 'SUBJECT "' . $criteria['subject'] . '"';
        }

        if (!empty($criteria['after'])) {
            $searchParts[] = 'SINCE "' . date('d-M-Y', strtotime($criteria['after'])) . '"';
        }

        if (!empty($criteria['before'])) {
            $searchParts[] = 'BEFORE "' . date('d-M-Y', strtotime($criteria['before'])) . '"';
        }

        return implode(' ', $searchParts);
    }

    /**
     * Get email statistics for designers inbox
     */
    public function getEmailStats(): array
    {
        try {
            $connection = $this->connectToImap();
            if (!$connection) {
                return ['error' => 'Failed to connect to IMAP server'];
            }

            $totalMessages = imap_num_msg($connection);
            $unreadMessages = imap_search($connection, 'UNSEEN') ? count(imap_search($connection, 'UNSEEN')) : 0;

            imap_close($connection);

            return [
                'total_messages' => $totalMessages,
                'unread_messages' => $unreadMessages,
                'read_messages' => $totalMessages - $unreadMessages,
                'email_address' => $this->imapUsername,
                'last_check' => now()->format('Y-m-d H:i:s'),
            ];

        } catch (\Exception $e) {
            Log::error('Error getting designers inbox stats: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get recent emails count for notifications
     */
    public function getRecentEmailsCount(int $hours = 24): int
    {
        try {
            $connection = $this->connectToImap();
            if (!$connection) {
                return 0;
            }

            $sinceDate = date('d-M-Y', strtotime("-{$hours} hours"));
            $recentEmails = imap_search($connection, "SINCE \"{$sinceDate}\"");

            imap_close($connection);

            return $recentEmails ? count($recentEmails) : 0;

        } catch (\Exception $e) {
            Log::error('Error getting recent emails count: ' . $e->getMessage());
            return 0;
        }
    }
}
