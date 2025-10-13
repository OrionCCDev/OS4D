<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\TaskEmailPreparation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TaskWaitingForReviewNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $task;
    protected $emailPreparation;
    protected $sender;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task, TaskEmailPreparation $emailPreparation, User $sender)
    {
        $this->task = $task;
        $this->emailPreparation = $emailPreparation;
        $this->sender = $sender;
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
        $toEmails = array_filter(array_map('trim', explode(',', $this->emailPreparation->to_emails)));
        $toEmailsString = implode(', ', $toEmails);

        return [
            'type' => 'task_waiting_for_review',
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->name,
            'email_preparation_id' => $this->emailPreparation->id,
            'to_emails' => $toEmailsString,
            'subject' => $this->emailPreparation->subject,
            'message' => 'Task "' . $this->task->title . '" is now waiting for client/consultant review after email was sent to: ' . $toEmailsString,
            'action_url' => route('tasks.show', $this->task->id),
            'action_text' => 'View Task Details',
            'badge_type' => 'warning',
            'icon' => 'bx-time',
        ];
    }
}
