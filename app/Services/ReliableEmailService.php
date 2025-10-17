<?php

namespace App\Services;

use App\Models\Email;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ReliableEmailService
{
    protected $imapHost;
    protected $imapPort;
    protected $imapUsername;
    protected $imapPassword;
    protected $imapFolder;

    public function __construct()
    {
        $this->imapHost = env('IMAP_HOST', 'mail.orion-contracting.com');
        $this->imapPort = env('IMAP_PORT', 993);
        $this->imapUsername = env('IMAP_USERNAME', 'engineering@orion-contracting.com');
        $this->imapPassword = env('IMAP_PASSWORD', '');
        $this->imapFolder = env('IMAP_FOLDER', 'INBOX');
    }

    /**
     * Fetch new emails with enhanced error handling
     */
    public function fetchNewEmails($maxResults = 50)
    {
        try {
            $this->log('Starting email fetch process...');

            // Connect to IMAP server
            $imap = $this->connectToImap();
            if (!$imap) {
                return [
                    'success' => false,
                    'errors' => ['Failed to connect to IMAP server'],
                    'emails' => [],
                    'total_fetched' => 0
                ];
            }

            // Get message count
            $messageCount = imap_num_msg($imap);
            $this->log("Found {$messageCount} messages in mailbox");

            if ($messageCount === 0) {
                imap_close($imap);
                return [
                    'success' => true,
                    'errors' => [],
                    'emails' => [],
                    'total_fetched' => 0
                ];
            }

            // Fetch recent messages
            $start = max(1, $messageCount - $maxResults + 1);
            $emails = [];

            for ($i = $messageCount; $i >= $start; $i--) {
                try {
                    $email = $this->parseEmailMessage($imap, $i);
                    if ($email) {
                        $emails[] = $email;
                    }
                } catch (\Exception $e) {
                    $this->log("Error parsing message {$i}: " . $e->getMessage());
                    continue;
                }
            }

            imap_close($imap);

            $this->log("Successfully fetched " . count($emails) . " emails");

            return [
                'success' => true,
                'errors' => [],
                'emails' => $emails,
                'total_fetched' => count($emails)
            ];

        } catch (\Exception $e) {
            $this->log("Exception in fetchNewEmails: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => [$e->getMessage()],
                'emails' => [],
                'total_fetched' => 0
            ];
        }
    }

    /**
     * Connect to IMAP server with retry logic
     */
    protected function connectToImap()
    {
        $maxRetries = 3;
        $retryDelay = 2; // seconds

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $this->log("IMAP connection attempt {$attempt}/{$maxRetries}");

                $connectionString = "{{$this->imapHost}:{$this->imapPort}/imap/ssl}{$this->imapFolder}";
                $imap = imap_open($connectionString, $this->imapUsername, $this->imapPassword);

                if ($imap) {
                    $this->log("IMAP connection successful");
                    return $imap;
                }

                $error = imap_last_error();
                $this->log("IMAP connection failed: {$error}");

            } catch (\Exception $e) {
                $this->log("IMAP connection exception: " . $e->getMessage());
            }

            if ($attempt < $maxRetries) {
                $this->log("Retrying in {$retryDelay} seconds...");
                sleep($retryDelay);
                $retryDelay *= 2; // Exponential backoff
            }
        }

        return false;
    }

    /**
     * Parse individual email message
     */
    protected function parseEmailMessage($imap, $messageNumber)
    {
        try {
            $header = imap_headerinfo($imap, $messageNumber);
            $body = imap_body($imap, $messageNumber);

            if (!$header) {
                return null;
            }

            // Decode subject
            $subject = $this->decodeEmailSubject($header->subject ?? 'No Subject');

            // Get sender email
            $fromEmail = '';
            if (isset($header->from[0])) {
                $fromEmail = $header->from[0]->mailbox . '@' . $header->from[0]->host;
            }

            // Get recipient emails
            $toEmails = [];
            if (isset($header->to)) {
                foreach ($header->to as $to) {
                    $toEmails[] = $to->mailbox . '@' . $to->host;
                }
            }

            // Get CC emails
            $ccEmails = [];
            if (isset($header->cc)) {
                foreach ($header->cc as $cc) {
                    $ccEmails[] = $cc->mailbox . '@' . $cc->host;
                }
            }

            // Check for attachments
            $hasAttachments = false;
            $structure = imap_fetchstructure($imap, $messageNumber);
            if ($structure && isset($structure->parts)) {
                foreach ($structure->parts as $part) {
                    if (isset($part->disposition) && strtolower($part->disposition) === 'attachment') {
                        $hasAttachments = true;
                        break;
                    }
                }
            }

            return [
                'message_id' => $header->message_id ?? uniqid(),
                'from_email' => $fromEmail,
                'to_emails' => $toEmails,
                'cc_emails' => $ccEmails,
                'subject' => $subject,
                'body' => $this->cleanEmailBody($body),
                'received_at' => date('Y-m-d H:i:s', $header->udate),
                'has_attachments' => $hasAttachments,
                'headers' => $header,
            ];

        } catch (\Exception $e) {
            $this->log("Error parsing message {$messageNumber}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Clean email body - properly parse MIME multipart emails
     */
    protected function cleanEmailBody(string $body): string
    {
        // If it's a MIME multipart email, extract the text/plain or text/html part
        if (strpos($body, 'Content-Type:') !== false) {
            $body = $this->extractTextFromMimeMultipart($body);
        }

        // Decode quoted-printable if needed
        if (strpos($body, 'quoted-printable') !== false) {
            $body = quoted_printable_decode($body);
        }

        // Decode base64 if needed
        if (strpos($body, 'base64') !== false) {
            $body = base64_decode($body);
        }

        // Remove MIME boundaries and headers
        $body = preg_replace('/--[a-zA-Z0-9]+/', '', $body);
        $body = preg_replace('/Content-Type:.*?\r?\n/', '', $body);
        $body = preg_replace('/Content-Transfer-Encoding:.*?\r?\n/', '', $body);
        $body = preg_replace('/charset=.*?\r?\n/', '', $body);

        // Remove excessive whitespace and clean up
        $body = preg_replace('/\r?\n\s*\r?\n/', "\n\n", $body);
        $body = preg_replace('/[ \t]+/', ' ', $body);
        $body = trim($body);

        return $body;
    }

    /**
     * Extract text content from MIME multipart email
     */
    protected function extractTextFromMimeMultipart(string $body): string
    {
        // Split by MIME boundaries
        $parts = preg_split('/--[a-zA-Z0-9]+/', $body);

        $textContent = '';
        $htmlContent = '';

        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part)) continue;

            // Check if this is text/plain
            if (strpos($part, 'Content-Type: text/plain') !== false) {
                // Extract content after headers
                $lines = explode("\n", $part);
                $contentStart = false;
                $content = '';

                foreach ($lines as $line) {
                    if ($contentStart) {
                        $content .= $line . "\n";
                    } elseif (trim($line) === '') {
                        $contentStart = true;
                    }
                }

                $textContent = trim($content);
                // Decode quoted-printable if needed
                if (strpos($part, 'quoted-printable') !== false) {
                    $textContent = quoted_printable_decode($textContent);
                }
            }

            // Check if this is text/html
            if (strpos($part, 'Content-Type: text/html') !== false) {
                // Extract content after headers
                $lines = explode("\n", $part);
                $contentStart = false;
                $content = '';

                foreach ($lines as $line) {
                    if ($contentStart) {
                        $content .= $line . "\n";
                    } elseif (trim($line) === '') {
                        $contentStart = true;
                    }
                }

                $html = trim($content);
                // Decode quoted-printable if needed
                if (strpos($part, 'quoted-printable') !== false) {
                    $html = quoted_printable_decode($html);
                }
                // Strip HTML tags for plain text display
                $htmlContent = strip_tags($html);
                $htmlContent = html_entity_decode($htmlContent, ENT_QUOTES, 'UTF-8');
            }
        }

        // Return text/plain if available, otherwise return stripped HTML
        if (!empty($textContent)) {
            return $textContent;
        }

        if (!empty($htmlContent)) {
            return $htmlContent;
        }

        // Fallback: return the original body
        return $body;
    }

    /**
     * Decode email subject from MIME encoding
     */
    protected function decodeEmailSubject(string $subject): string
    {
        try {
            // If subject is already decoded or doesn't contain MIME encoding, return as is
            if (!preg_match('/=\?[^?]+\?[QB]\?[^?]+\?=/i', $subject)) {
                return $subject;
            }

            // Decode MIME header encoding
            $decoded = mb_decode_mimeheader($subject);

            // If decoding failed, try alternative method
            if ($decoded === $subject) {
                $decoded = iconv_mime_decode($subject, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
            }

            return $decoded ?: $subject;
        } catch (\Exception $e) {
            $this->log('Failed to decode email subject: ' . $e->getMessage());
            return $subject;
        }
    }

    /**
     * Store emails in database
     */
    public function storeEmailsInDatabase($emails, User $manager)
    {
        $stored = 0;
        $skipped = 0;
        $storedEmails = [];

        foreach ($emails as $emailData) {
            try {
                // Check if email already exists
                $existingEmail = Email::where('message_id', $emailData['message_id'])->first();

                if ($existingEmail) {
                    $skipped++;
                    continue;
                }

                // Create new email record
                $email = Email::create([
                    'message_id' => $emailData['message_id'],
                    'from_email' => $emailData['from_email'],
                    'to_emails' => json_encode($emailData['to_emails']),
                    'cc_emails' => json_encode($emailData['cc_emails']),
                    'subject' => $emailData['subject'],
                    'body' => $emailData['body'],
                    'received_at' => $emailData['received_at'],
                    'has_attachments' => $emailData['has_attachments'],
                    'user_id' => $manager->id,
                ]);

                $stored++;
                $storedEmails[] = $email;

                $this->log("Stored email: {$email->subject} from {$email->from_email}");

            } catch (\Exception $e) {
                $this->log("Error storing email: " . $e->getMessage());
                continue;
            }
        }

        return [
            'stored' => $stored,
            'skipped' => $skipped,
            'stored_emails' => $storedEmails
        ];
    }

    /**
     * Log messages
     */
    protected function log($message)
    {
        Log::info("ReliableEmailService: {$message}");
    }
}
