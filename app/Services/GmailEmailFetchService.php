<?php

namespace App\Services;

use App\Models\User;
use App\Models\Email;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GmailEmailFetchService
{
    protected $gmailOAuthService;

    public function __construct(GmailOAuthService $gmailOAuthService)
    {
        $this->gmailOAuthService = $gmailOAuthService;
    }

    /**
     * Fetch all emails from Gmail for a user
     */
    public function fetchAllEmails(User $user, int $maxResults = 100): array
    {
        $result = [
            'success' => false,
            'emails' => [],
            'total_fetched' => 0,
            'errors' => []
        ];

        try {
            $gmailService = $this->gmailOAuthService->getGmailService($user);
            if (!$gmailService) {
                $result['errors'][] = 'Gmail service not available. Please ensure Gmail is connected.';
                return $result;
            }

            // Get list of messages
            $messages = $this->getMessageList($gmailService, $maxResults);

            if (empty($messages)) {
                $result['success'] = true;
                $result['message'] = 'No emails found';
                return $result;
            }

            // Fetch detailed information for each message
            $emails = [];
            foreach ($messages as $message) {
                $emailData = $this->getEmailDetails($gmailService, $message->getId());
                if ($emailData) {
                    $emails[] = $emailData;
                }
            }

            $result['success'] = true;
            $result['emails'] = $emails;
            $result['total_fetched'] = count($emails);

            Log::info('Successfully fetched ' . count($emails) . ' emails for user: ' . $user->id);

        } catch (\Exception $e) {
            Log::error('Error fetching emails for user ' . $user->id . ': ' . $e->getMessage());
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Get list of message IDs from Gmail
     */
    protected function getMessageList(Gmail $gmailService, int $maxResults): array
    {
        try {
            $optParams = [
                'maxResults' => $maxResults,
                'includeSpamTrash' => false
            ];

            $messages = $gmailService->users_messages->listUsersMessages('me', $optParams);
            return $messages->getMessages() ?? [];

        } catch (\Exception $e) {
            Log::error('Error getting message list: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get detailed email information
     */
    protected function getEmailDetails(Gmail $gmailService, string $messageId): ?array
    {
        try {
            $message = $gmailService->users_messages->get('me', $messageId, [
                'format' => 'full'
            ]);

            $headers = $message->getPayload()->getHeaders();
            $headerMap = [];

            foreach ($headers as $header) {
                $headerMap[$header->getName()] = $header->getValue();
            }

            // Extract email data
            $emailData = [
                'gmail_message_id' => $messageId,
                'thread_id' => $message->getThreadId(),
                'from_email' => $this->extractEmailFromHeader($headerMap['From'] ?? ''),
                'to_email' => $this->extractEmailFromHeader($headerMap['To'] ?? ''),
                'cc_emails' => $this->extractCcEmails($headerMap['Cc'] ?? ''),
                'subject' => $headerMap['Subject'] ?? 'No Subject',
                'date' => $this->parseGmailDate($headerMap['Date'] ?? ''),
                'body' => $this->extractEmailBody($message->getPayload()),
                'attachments' => $this->extractAttachments($message->getPayload()),
                'message_id' => $headerMap['Message-ID'] ?? '',
                'in_reply_to' => $headerMap['In-Reply-To'] ?? null,
                'references' => $headerMap['References'] ?? null,
                'labels' => $message->getLabelIds() ?? [],
                'snippet' => $message->getSnippet() ?? '',
                'size_estimate' => $message->getSizeEstimate() ?? 0,
            ];

            return $emailData;

        } catch (\Exception $e) {
            Log::error('Error getting email details for message ' . $messageId . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract email address from header
     */
    protected function extractEmailFromHeader(string $header): string
    {
        if (preg_match('/<(.+?)>/', $header, $matches)) {
            return $matches[1];
        }

        if (filter_var($header, FILTER_VALIDATE_EMAIL)) {
            return $header;
        }

        return $header;
    }

    /**
     * Extract CC emails
     */
    protected function extractCcEmails(string $ccHeader): array
    {
        if (empty($ccHeader)) {
            return [];
        }

        $emails = [];
        $parts = explode(',', $ccHeader);

        foreach ($parts as $part) {
            $email = trim($this->extractEmailFromHeader($part));
            if (!empty($email)) {
                $emails[] = $email;
            }
        }

        return $emails;
    }

    /**
     * Parse Gmail date format
     */
    protected function parseGmailDate(string $dateString): ?Carbon
    {
        try {
            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            Log::warning('Could not parse date: ' . $dateString);
            return null;
        }
    }

    /**
     * Extract email body from payload
     */
    protected function extractEmailBody($payload): string
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
     * Extract attachments from payload
     */
    protected function extractAttachments($payload): array
    {
        $attachments = [];

        if ($payload->getParts()) {
            foreach ($payload->getParts() as $part) {
                if ($part->getFilename()) {
                    $attachments[] = [
                        'filename' => $part->getFilename(),
                        'mime_type' => $part->getMimeType(),
                        'size' => $part->getBody()->getSize() ?? 0,
                        'attachment_id' => $part->getBody()->getAttachmentId() ?? null,
                    ];
                }
            }
        }

        return $attachments;
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
                $existingEmail = Email::where('gmail_message_id', $emailData['gmail_message_id'])->first();

                if ($existingEmail) {
                    $result['skipped']++;
                    continue;
                }

                // Create new email record
                Email::create([
                    'from_email' => $emailData['from_email'],
                    'to_email' => $emailData['to_email'],
                    'cc_emails' => $emailData['cc_emails'],
                    'subject' => $emailData['subject'],
                    'body' => $emailData['body'],
                    'received_at' => $emailData['date'] ?? now(),
                    'status' => 'received',
                    'gmail_message_id' => $emailData['gmail_message_id'],
                    'thread_id' => $emailData['thread_id'],
                    'message_id' => $emailData['message_id'],
                    'reply_to_email_id' => $emailData['in_reply_to'] ? $this->findReplyToEmail($emailData['in_reply_to']) : null,
                    'attachments' => $emailData['attachments'],
                    'user_id' => $user->id,
                    'email_type' => 'received',
                ]);

                $result['stored']++;

            } catch (\Exception $e) {
                Log::error('Error storing email: ' . $e->getMessage());
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
    public function searchEmails(User $user, array $criteria = []): array
    {
        $result = [
            'success' => false,
            'emails' => [],
            'total_found' => 0,
            'errors' => []
        ];

        try {
            $gmailService = $this->gmailOAuthService->getGmailService($user);
            if (!$gmailService) {
                $result['errors'][] = 'Gmail service not available';
                return $result;
            }

            // Build search query
            $query = $this->buildSearchQuery($criteria);

            $optParams = [
                'q' => $query,
                'maxResults' => $criteria['maxResults'] ?? 50
            ];

            $messages = $gmailService->users_messages->listUsersMessages('me', $optParams);
            $messageList = $messages->getMessages() ?? [];

            $emails = [];
            foreach ($messageList as $message) {
                $emailData = $this->getEmailDetails($gmailService, $message->getId());
                if ($emailData) {
                    $emails[] = $emailData;
                }
            }

            $result['success'] = true;
            $result['emails'] = $emails;
            $result['total_found'] = count($emails);

        } catch (\Exception $e) {
            Log::error('Error searching emails: ' . $e->getMessage());
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Build Gmail search query
     */
    protected function buildSearchQuery(array $criteria): string
    {
        $queryParts = [];

        if (!empty($criteria['from'])) {
            $queryParts[] = 'from:' . $criteria['from'];
        }

        if (!empty($criteria['to'])) {
            $queryParts[] = 'to:' . $criteria['to'];
        }

        if (!empty($criteria['subject'])) {
            $queryParts[] = 'subject:' . $criteria['subject'];
        }

        if (!empty($criteria['has_attachment'])) {
            $queryParts[] = 'has:attachment';
        }

        if (!empty($criteria['after'])) {
            $queryParts[] = 'after:' . $criteria['after'];
        }

        if (!empty($criteria['before'])) {
            $queryParts[] = 'before:' . $criteria['before'];
        }

        if (!empty($criteria['domain'])) {
            $queryParts[] = 'from:' . $criteria['domain'];
        }

        return implode(' ', $queryParts);
    }

    /**
     * Get email statistics
     */
    public function getEmailStats(User $user): array
    {
        try {
            $gmailService = $this->gmailOAuthService->getGmailService($user);
            if (!$gmailService) {
                return ['error' => 'Gmail service not available'];
            }

            $profile = $gmailService->users->getProfile('me');

            return [
                'total_messages' => $profile->getMessagesTotal() ?? 0,
                'total_threads' => $profile->getThreadsTotal() ?? 0,
                'email_address' => $profile->getEmailAddress() ?? '',
                'history_id' => $profile->getHistoryId() ?? 0,
            ];

        } catch (\Exception $e) {
            Log::error('Error getting email stats: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
