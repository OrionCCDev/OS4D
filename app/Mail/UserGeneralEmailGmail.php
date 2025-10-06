<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class UserGeneralEmailGmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    public $sender;
    public $toEmails;

    /**
     * Create a new message instance.
     */
    public function __construct(string $subject, string $body, $sender, array $toEmails)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->sender = $sender;
        $this->toEmails = $toEmails;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
            from: $this->sender->email, // Use user's email as sender
            replyTo: $this->sender->email, // Use user's email for replies
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user-general-email-gmail',
            with: [
                'bodyContent' => $this->body,
                'senderName' => $this->sender->name,
                'senderEmail' => $this->sender->email,
                'toRecipients' => $this->toEmails,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        $attachments = [];
        $logoPath = public_path('uploads/logo-blue.webp');
        if (file_exists($logoPath)) {
            $attachments[] = Attachment::fromPath($logoPath)->as('logo.webp')->withMime('image/webp');
        }
        return $attachments;
    }
}
