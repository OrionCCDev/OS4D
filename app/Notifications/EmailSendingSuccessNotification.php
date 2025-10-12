<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Task;
use App\Models\TaskEmailPreparation;

class EmailSendingSuccessNotification extends Notification
{
    use Queueable;

    protected $task;
    protected $emailPreparation;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task, TaskEmailPreparation $emailPreparation)
    {
        $this->task = $task;
        $this->emailPreparation = $emailPreparation;
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
            'type' => 'email_sent_success',
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'email_preparation_id' => $this->emailPreparation->id,
            'to_emails' => $this->emailPreparation->to_emails,
            'subject' => $this->emailPreparation->subject,
            'message' => "Email sent successfully for task: {$this->task->title} to: {$this->emailPreparation->to_emails}",
            'action_url' => route('tasks.show', $this->task->id),
            'action_text' => 'View Task',
        ];
    }
}

