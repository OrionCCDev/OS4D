<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SimpleTestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    public $sender;

    /**
     * Create a new message instance.
     */
    public function __construct(string $subject, string $body, $sender)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->sender = $sender;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
            from: $this->sender->email,
            replyTo: $this->sender->email,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.simple-test',
            with: [
                'bodyContent' => $this->body,
                'senderName' => $this->sender->name,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return []; // No attachments
    }
}
