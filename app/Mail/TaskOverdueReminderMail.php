<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskOverdueReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Task $task;
    public User $manager;
    public ?string $customMessage;

    /**
     * Create a new message instance.
     */
    public function __construct(Task $task, User $manager, ?string $customMessage = null)
    {
        $this->task = $task;
        $this->manager = $manager;
        $this->customMessage = $customMessage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Overdue Task Reminder – ' . $this->task->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.task-overdue-reminder',
            with: [
                'task' => $this->task,
                'manager' => $this->manager,
                'customMessage' => $this->customMessage,
                'overdueDuration' => $this->formatDuration(),
            ],
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

    private function formatDuration(): string
    {
        if (!$this->task->due_date) {
            return 'Unknown';
        }

        $due = $this->task->due_date->copy()->startOfDay();
        $now = now()->startOfDay();

        if ($due->gte($now)) {
            return 'Due today';
        }

        $diff = $due->diff($now);
        $parts = [];

        if ($diff->d > 0) {
            $parts[] = $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
        }

        if (empty($parts)) {
            $parts[] = 'less than 1 day';
        }

        return implode(' ', $parts);
    }
}

