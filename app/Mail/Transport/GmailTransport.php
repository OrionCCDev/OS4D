<?php

namespace App\Mail\Transport;

use App\Services\GmailOAuthService;
use Illuminate\Mail\Transport\Transport;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mime\Email;

class GmailTransport extends Transport
{
    protected $gmailOAuthService;
    protected $user;

    public function __construct(GmailOAuthService $gmailOAuthService, $user = null)
    {
        $this->gmailOAuthService = $gmailOAuthService;
        $this->user = $user;
    }

    /**
     * Set the user for this transport instance
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Send the message
     */
    public function send(\Symfony\Component\Mailer\SentMessage $message, \Symfony\Component\Mailer\Envelope $envelope = null): void
    {
        if (!$this->user) {
            throw new \Exception('No user specified for Gmail transport');
        }

        if (!$this->gmailOAuthService->isConnected($this->user)) {
            throw new \Exception('User does not have Gmail connected');
        }

        try {
            $email = $message->getOriginalMessage();
            $emailData = $this->convertEmailToGmailFormat($email);

            $success = $this->gmailOAuthService->sendEmail($this->user, $emailData);

            if (!$success) {
                throw new \Exception('Failed to send email via Gmail API');
            }
        } catch (\Exception $e) {
            Log::error('Gmail transport error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Convert Symfony Email to Gmail API format
     */
    protected function convertEmailToGmailFormat(Email $email): array
    {
        $from = $this->user->email; // Use user's email as sender
        $to = $this->extractEmailAddresses($email->getTo());
        $cc = $this->extractEmailAddresses($email->getCc());
        $bcc = $this->extractEmailAddresses($email->getBcc());
        $subject = $email->getSubject();
        $body = $email->getHtmlBody() ?: $email->getTextBody();

        $emailData = [
            'from' => $from,
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
        ];

        if (!empty($cc)) {
            $emailData['cc'] = $cc;
        }

        if (!empty($bcc)) {
            $emailData['bcc'] = $bcc;
        }

        // Handle attachments
        $attachments = [];
        foreach ($email->getAttachments() as $attachment) {
            $attachments[] = [
                'filename' => $attachment->getFilename(),
                'content' => $attachment->getBody(),
                'mime_type' => $attachment->getMediaType() . '/' . $attachment->getMediaSubtype(),
            ];
        }

        if (!empty($attachments)) {
            $emailData['attachments'] = $attachments;
        }

        return $emailData;
    }

    /**
     * Extract email addresses from address list
     */
    protected function extractEmailAddresses(array $addresses): array
    {
        $emails = [];
        foreach ($addresses as $address) {
            $emails[] = $address->getAddress();
        }
        return $emails;
    }
}
