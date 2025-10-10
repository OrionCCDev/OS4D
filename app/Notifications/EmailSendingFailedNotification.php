<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Task;
use App\Models\TaskEmailPreparation;

class EmailSendingFailedNotification extends Notification
{
    use Queueable;

    protected $task;
    protected $emailPreparation;
    protected $errorMessage;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task, TaskEmailPreparation $emailPreparation, string $errorMessage)
    {
        $this->task = $task;
        $this->emailPreparation = $emailPreparation;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'email_sending_failed',
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'email_preparation_id' => $this->emailPreparation->id,
            'to_emails' => $this->emailPreparation->to_emails,
            'subject' => $this->emailPreparation->subject,
            'error_message' => $this->errorMessage,
            'message' => "Failed to send email for task: {$this->task->title}",
            'action_url' => route('tasks.show', $this->task->id),
            'action_text' => 'View Task and Retry',
        ];
    }
}

