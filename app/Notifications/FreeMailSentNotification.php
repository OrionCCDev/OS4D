<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\TaskEmailPreparation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FreeMailSentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $task;
    protected $emailPreparation;
    protected $sender;
    protected $toEmails;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task, TaskEmailPreparation $emailPreparation, User $sender, string $toEmails)
    {
        $this->task = $task;
        $this->emailPreparation = $emailPreparation;
        $this->sender = $sender;
        $this->toEmails = $toEmails;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Free Mail Sent - Task: ' . $this->task->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->sender->name . ' has sent a free mail for task: ' . $this->task->title)
            ->line('**Recipients:** ' . $this->toEmails)
            ->line('**Subject:** ' . $this->emailPreparation->subject)
            ->action('View Task Details', route('tasks.show', $this->task->id))
            ->action('View Email Details', route('tasks.free-mail', $this->task->id))
            ->line('You can check the email details and task progress using the links above.')
            ->salutation('Best regards, Orion Designers System');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'free_mail_sent',
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->name,
            'to_emails' => $this->toEmails,
            'subject' => $this->emailPreparation->subject,
            'email_preparation_id' => $this->emailPreparation->id,
            'message' => $this->sender->name . ' sent a free mail for task "' . $this->task->title . '" to: ' . $this->toEmails,
            'task_url' => route('tasks.show', $this->task->id),
            'email_url' => route('tasks.free-mail', $this->task->id),
        ];
    }
}
