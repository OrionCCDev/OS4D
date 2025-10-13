<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\TaskEmailPreparation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ManagerEmailNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $task;
    public $emailPreparation;
    public $sender;
    public $manager;
    public $toEmails;
    public $ccEmails;

    /**
     * Create a new message instance.
     */
    public function __construct(
        Task $task,
        TaskEmailPreparation $emailPreparation,
        User $sender,
        User $manager,
        array $toEmails = [],
        array $ccEmails = []
    ) {
        $this->task = $task;
        $this->emailPreparation = $emailPreparation;
        $this->sender = $sender;
        $this->manager = $manager;
        $this->toEmails = $toEmails;
        $this->ccEmails = $ccEmails;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[NOTIFICATION] Confirmation Email Sent - Task #' . $this->task->id . ': ' . $this->task->title,
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
            view: 'emails.manager-email-notification',
            with: [
                'task' => $this->task,
                'emailPreparation' => $this->emailPreparation,
                'sender' => $this->sender,
                'manager' => $this->manager,
                'toEmails' => $this->toEmails,
                'ccEmails' => $this->ccEmails,
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
