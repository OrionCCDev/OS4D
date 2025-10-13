<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EngineeringInboxReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $emailData;
    public $manager;
    public $relatedTask;

    /**
     * Create a new message instance.
     */
    public function __construct(array $emailData, User $manager, ?Task $relatedTask = null)
    {
        $this->emailData = $emailData;
        $this->manager = $manager;
        $this->relatedTask = $relatedTask;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = '[NEW EMAIL] ' . ($this->emailData['subject'] ?? 'No Subject');
        if ($this->relatedTask) {
            $subject .= ' - Task #' . $this->relatedTask->id;
        }

        return new Envelope(
            subject: $subject,
            from: 'engineering@orion-contracting.com',
            replyTo: 'engineering@orion-contracting.com',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.engineering-inbox-received',
            with: [
                'emailData' => $this->emailData,
                'manager' => $this->manager,
                'relatedTask' => $this->relatedTask,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
