<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskApprovalInternalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $task;
    public $user;
    public $approver;

    /**
     * Create a new message instance.
     */
    public function __construct(Task $task, User $user, User $approver)
    {
        $this->task = $task;
        $this->user = $user;
        $this->approver = $approver;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Task Approved: ' . $this->task->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.task-approval-internal',
            with: [
                'task' => $this->task,
                'user' => $this->user,
                'approver' => $this->approver,
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
