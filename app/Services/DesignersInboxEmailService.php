<?php

namespace App\Services;

use App\Models\Email;
use App\Models\User;
use App\Models\EmailFetchLog;
use App\Services\DesignersInboxNotificationService;
use App\Services\NotificationService;
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

    public function __construct(NotificationService $notificationService)
    {
        $this->imapHost = config('mail.imap.host', 'mail.orion-contracting.com');
        $this->imapPort = config('mail.imap.port', 993);
        $this->imapUsername = config('mail.imap.username', 'engineering@orion-contracting.com');
        $this->imapPassword = config('mail.imap.password', '');
        $this->imapFolder = config('mail.imap.folder', 'INBOX');
        $this->notificationService = $notificationService;
    }

    /**
     * Fetch new emails from designers inbox (incremental)
     */
    public function fetchNewEmails(int $maxResults = 100): array
    {
        $result = [
            'success' => false,
            'emails' => [],
            'total_fetched' => 0,
            'current_message_count' => 0,
            'errors' => []
        ];

        try {
            $connection = $this->connectToImap();
            if (!$connection) {
                $result['errors'][] = 'Failed to connect to IMAP server';
                return $result;
            }

            // Get the latest fetch log
            $lastFetchLog = EmailFetchLog::getLatestForSource('designers_inbox');
            $lastMessageCount = $lastFetchLog ? $lastFetchLog->last_message_count : 0;

            // Get current message count
            $currentMessageCount = imap_num_msg($connection);
            $result['current_message_count'] = $currentMessageCount;
            Log::info("Current message count: {$currentMessageCount}, Last fetch count: {$lastMessageCount}");

            // Only fetch new messages
            if ($currentMessageCount <= $lastMessageCount) {
                Log::info("No new messages since last fetch. Current: {$currentMessageCount}, Last: {$lastMessageCount}");
                imap_close($connection);
                $result['success'] = true;
                $result['total_fetched'] = 0;
                return $result;
            }

            // Calculate range of new messages
            $newMessageCount = $currentMessageCount - $lastMessageCount;
            $messagesToFetch = min($newMessageCount, $maxResults);
            $start = $currentMessageCount - $messagesToFetch + 1;

            Log::info("Fetching {$messagesToFetch} new messages from position {$start} to {$currentMessageCount}");

            $emails = [];
            for ($i = $start; $i <= $currentMessageCount; $i++) {
                $emailData = $this->parseEmailMessage($connection, $i);
                if ($emailData) {
                    $emails[] = $emailData;
                }
            }

            imap_close($connection);

            $result['success'] = true;
            $result['emails'] = $emails;
            $result['total_fetched'] = count($emails);

            Log::info('Successfully fetched ' . count($emails) . ' new emails from designers inbox');

        } catch (\Exception $e) {
            Log::error('Error fetching new emails from designers inbox: ' . $e->getMessage());
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Fetch all emails from designers inbox (legacy method for manual fetching)
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

            Log::info('IMAP connection successful to engineering@orion-contracting.com');
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
                'message_id' => $header->message_id ?? 'msg-' . $messageNumber . '-' . $header->date,
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
     * Store fetched emails in database with enhanced duplication prevention
     */
    public function storeEmailsInDatabase(array $emails, User $user): array
    {
        $result = [
            'stored' => 0,
            'skipped' => 0,
            'stored_emails' => [],
            'errors' => []
        ];

        foreach ($emails as $emailData) {
            try {
                // Enhanced duplication check - check multiple fields
                $existingEmail = $this->checkForDuplicateEmail($emailData);

                if ($existingEmail) {
                    Log::info("Skipping duplicate email: {$emailData['subject']} from {$emailData['from_email']} (existing ID: {$existingEmail->id})");
                    $result['skipped']++;
                    continue;
                }

                // Create new email record
                $email = Email::create([
                    'from_email' => $emailData['from_email'],
                    'to_email' => $emailData['to_email'],
                    'cc' => is_array($emailData['cc_emails']) ? implode(', ', $emailData['cc_emails']) : $emailData['cc_emails'],
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

                // Add to stored emails array
                $result['stored_emails'][] = $email;

                // Check if this is a reply and process it
                Log::info("Processing email for reply detection: {$emailData['subject']} from {$emailData['from_email']}");
                $this->processReplyIfApplicable($email, $emailData);

                Log::info("Stored new email: {$emailData['subject']} from {$emailData['from_email']}");
                $result['stored']++;

            } catch (\Exception $e) {
                Log::error('Error storing designers inbox email: ' . $e->getMessage());
                $result['errors'][] = $e->getMessage();
            }
        }

        return $result;
    }

    /**
     * Enhanced duplication check using multiple criteria
     */
    protected function checkForDuplicateEmail(array $emailData): ?Email
    {
        // Check by message_id (primary check) - this is the most reliable
        if (!empty($emailData['message_id'])) {
            $existing = Email::where('message_id', $emailData['message_id'])
                ->where('email_source', 'designers_inbox')
                ->first();
            if ($existing) {
                Log::info("Found duplicate by message_id: {$emailData['message_id']} (existing ID: {$existing->id})");
                return $existing;
            }
        }

        // For replies, be more lenient with duplication checks
        $isReply = $this->isReplyEmail($emailData['subject']);

        if ($isReply) {
            Log::info("Email is a reply, using lenient duplication check: {$emailData['subject']}");
            // For replies, only check exact message_id match
            // Don't use subject/body matching as replies can have similar content
            return null;
        }

        // For non-replies, use stricter duplication checks but with shorter time window
        // Check by subject + from_email + received_at (secondary check)
        if (!empty($emailData['subject']) && !empty($emailData['from_email']) && !empty($emailData['date'])) {
            $existing = Email::where('subject', $emailData['subject'])
                ->where('from_email', $emailData['from_email'])
                ->where('email_source', 'designers_inbox')
                ->whereBetween('received_at', [
                    Carbon::parse($emailData['date'])->subMinutes(2),
                    Carbon::parse($emailData['date'])->addMinutes(2)
                ])
                ->first();
            if ($existing) {
                Log::info("Found duplicate by subject+from+date: {$emailData['subject']}");
                return $existing;
            }
        }

        // Check by subject + from_email + body hash (tertiary check)
        if (!empty($emailData['subject']) && !empty($emailData['from_email']) && !empty($emailData['body'])) {
            $bodyHash = md5(substr($emailData['body'], 0, 1000)); // Hash first 1000 chars
            $existing = Email::where('subject', $emailData['subject'])
                ->where('from_email', $emailData['from_email'])
                ->where('email_source', 'designers_inbox')
                ->whereRaw('MD5(SUBSTRING(body, 1, 1000)) = ?', [$bodyHash])
                ->first();
            if ($existing) {
                Log::info("Found duplicate by subject+from+body: {$emailData['subject']}");
                return $existing;
            }
        }

        return null;
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
     * Process reply if this email is a reply to an existing email
     */
    protected function processReplyIfApplicable(Email $email, array $emailData): void
    {
        try {
            // Check if this is a reply email
            if (!$this->isReplyEmail($emailData['subject'])) {
                return;
            }

            Log::info("Processing potential reply: {$emailData['subject']}");

            // Try to find the original email this is replying to
            $originalEmail = $this->findOriginalEmailForReply($emailData);

            if ($originalEmail) {
                // Update the email record to link it to the original
                $email->update([
                    'reply_to_email_id' => $originalEmail->id,
                    'user_id' => $originalEmail->user_id,
                    'task_id' => $originalEmail->task_id,
                ]);

                // Mark original email as replied
                $originalEmail->update([
                    'status' => 'replied',
                    'replied_at' => now()
                ]);

                // Create reply notification using unified notification service
                $this->notificationService->createReplyNotification($email);

                Log::info("Reply processed successfully for email ID: {$originalEmail->id}");
            } else {
                Log::info("Could not find original email for reply: {$emailData['subject']}");
            }

        } catch (\Exception $e) {
            Log::error('Error processing reply: ' . $e->getMessage());
        }
    }


    /**
     * Find original email this is replying to
     */
    protected function findOriginalEmailForReply(array $emailData): ?Email
    {
        try {
            // Method 1: Try to find by in_reply_to header
            if (!empty($emailData['in_reply_to'])) {
                $originalEmail = Email::where('message_id', $emailData['in_reply_to'])
                    ->where('email_source', 'designers_inbox')
                    ->first();
                if ($originalEmail) {
                    Log::info("Found original email by in_reply_to: {$originalEmail->id}");
                    return $originalEmail;
                }
            }

            // Method 2: Try to find by subject (remove "Re:" prefix)
            $cleanSubject = preg_replace('/^(Re:|RE:|Fwd:|FWD:)\s*/i', '', $emailData['subject']);
            $originalEmail = Email::where('subject', $cleanSubject)
                ->where('email_source', 'designers_inbox')
                ->where('email_type', 'sent')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($originalEmail) {
                Log::info("Found original email by subject: {$originalEmail->id}");
                return $originalEmail;
            }

            // Method 3: Try to find by from_email and similar subject
            if (!empty($emailData['from_email'])) {
                $originalEmail = Email::where('to_email', $emailData['from_email'])
                    ->where('email_source', 'designers_inbox')
                    ->where('email_type', 'sent')
                    ->where('subject', 'like', '%' . $cleanSubject . '%')
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($originalEmail) {
                    Log::info("Found original email by from_email and subject: {$originalEmail->id}");
                    return $originalEmail;
                }
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Error finding original email: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create reply notification
     */
    protected function createReplyNotification(Email $originalEmail, Email $replyEmail): void
    {
        try {
            // Create notification for the user who sent the original email
            if ($originalEmail->user_id) {
                $notificationService = app(\App\Services\NotificationService::class);
                $user = \App\Models\User::find($originalEmail->user_id);

                if ($user) {
                    $notificationService->notifyEmailReply($originalEmail, $replyEmail, $user);
                    Log::info("Reply notification created for user {$originalEmail->user_id}");
                }
            }

        } catch (\Exception $e) {
            Log::error('Error creating reply notification: ' . $e->getMessage());
        }
    }

    /**
     * Log fetch operation results
     */
    public function logFetchOperation(array $fetchResult, array $storeResult, int $currentMessageCount): void
    {
        try {
            $lastMessageId = null;
            if (!empty($fetchResult['emails'])) {
                $lastMessageId = end($fetchResult['emails'])['message_id'] ?? null;
            }

            EmailFetchLog::updateFetchLog('designers_inbox', [
                'last_fetch_at' => now(),
                'last_message_id' => $lastMessageId,
                'last_message_count' => $currentMessageCount,
                'total_fetched' => $fetchResult['total_fetched'],
                'total_stored' => $storeResult['stored'],
                'total_skipped' => $storeResult['skipped'],
                'last_errors' => array_merge($fetchResult['errors'] ?? [], $storeResult['errors'] ?? [])
            ]);

            Log::info('Email fetch operation logged', [
                'fetched' => $fetchResult['total_fetched'],
                'stored' => $storeResult['stored'],
                'skipped' => $storeResult['skipped'],
                'current_message_count' => $currentMessageCount
            ]);

        } catch (\Exception $e) {
            Log::error('Error logging fetch operation: ' . $e->getMessage());
        }
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
