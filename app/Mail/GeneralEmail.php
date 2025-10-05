<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class GeneralEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    public $sender;
    public $recipients;

    /**
     * Create a new message instance.
     */
    public function __construct($subject, $body, User $sender, $recipients = [])
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->sender = $sender;
        $this->recipients = $recipients;
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
            view: 'emails.general-email',
            with: [
                'subject' => $this->subject,
                'body' => $this->body,
                'sender' => $this->sender,
                'recipients' => $this->recipients,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
