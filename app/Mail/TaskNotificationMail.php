<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\ExternalStakeholder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $task;
    public $stakeholder;
    public $notificationType;
    public $message;

    /**
     * Create a new message instance.
     */
    public function __construct(Task $task, ExternalStakeholder $stakeholder, string $notificationType, string $message)
    {
        $this->task = $task;
        $this->stakeholder = $stakeholder;
        $this->notificationType = $notificationType;
        $this->message = $message;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match($this->notificationType) {
            'assigned' => 'Task Assigned',
            'accepted' => 'Task Accepted',
            'submitted_for_review' => 'Task Submitted for Review',
            'approved' => 'Task Approved',
            'rejected' => 'Task Rejected',
            'completed' => 'Task Completed',
            default => 'Task Update'
        };

        return new Envelope(
            subject: $subject . ' - ' . $this->task->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.task-notification',
            with: [
                'task' => $this->task,
                'stakeholder' => $this->stakeholder,
                'notificationType' => $this->notificationType,
                'message' => $this->message,
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
