<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use App\Models\TaskEmailPreparation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ManagerTaskWaitingForReviewNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $task;
    public $emailPreparation;
    public $sender;
    public $manager;
    public $toEmails;
    public $ccEmails;

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

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[NOTIFICATION] Task Waiting for Client/Consultant Review - Task #' . $this->task->id . ': ' . $this->task->title,
            from: 'engineering@orion-contracting.com',
            to: $this->manager->email,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.manager-task-waiting-for-review-notification',
            with: [
                'task' => $this->task,
                'emailPreparation' => $this->emailPreparation,
                'sender' => $this->sender,
                'manager' => $this->manager,
                'toEmails' => $this->toEmails,
                'ccEmails' => $this->ccEmails,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
