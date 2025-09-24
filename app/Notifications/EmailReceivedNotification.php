<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Email;
use App\Models\Task;

class EmailReceivedNotification extends Notification
{
    use Queueable;

    public $email;
    public $task;

    /**
     * Create a new notification instance.
     */
    public function __construct(Email $email, Task $task = null)
    {
        $this->email = $email;
        $this->task = $task;
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
        $message = (new MailMessage)
            ->subject('New Email Received - ' . $this->email->subject)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have received a new email.')
            ->line('**From:** ' . $this->email->from_email)
            ->line('**Subject:** ' . $this->email->subject)
            ->line('**Received:** ' . $this->email->formatted_received_date);

        if ($this->task) {
            $message->line('**Related Task:** ' . $this->task->title)
                ->action('View Task', url('/tasks/' . $this->task->id));
        }

        $message->line('**Preview:** ' . $this->email->preview)
            ->action('View Email', url('/emails/' . $this->email->id))
            ->line('Thank you for using our task management system!');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'email_received',
            'email_id' => $this->email->id,
            'from_email' => $this->email->from_email,
            'subject' => $this->email->subject,
            'received_at' => $this->email->received_at,
            'task_id' => $this->task?->id,
            'task_title' => $this->task?->title,
            'preview' => $this->email->preview,
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'email_received';
    }
}
