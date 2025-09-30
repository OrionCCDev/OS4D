<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Email;
use App\Models\Task;

class EmailReplyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $originalEmail;
    public $replyEmail;
    public $task;

    /**
     * Create a new notification instance.
     */
    public function __construct(Email $originalEmail, Email $replyEmail, Task $task = null)
    {
        $this->originalEmail = $originalEmail;
        $this->replyEmail = $replyEmail;
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
            ->subject('📧 Email Reply Received - ' . $this->originalEmail->subject)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have received a reply to your email.')
            ->line('**Original Email:** ' . $this->originalEmail->subject)
            ->line('**Reply From:** ' . $this->replyEmail->from_email)
            ->line('**Reply Subject:** ' . $this->replyEmail->subject)
            ->line('**Received:** ' . $this->replyEmail->received_at->format('M d, Y \a\t g:i A'));

        if ($this->task) {
            $message->line('**Related Task:** ' . $this->task->title)
                ->action('View Task', url('/tasks/' . $this->task->id));
        }

        $message->line('**Reply Preview:** ' . substr(strip_tags($this->replyEmail->body), 0, 100) . '...')
            ->action('View Reply', url('/emails/' . $this->replyEmail->id . '/show'))
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
            'type' => 'email_reply_received',
            'original_email_id' => $this->originalEmail->id,
            'reply_email_id' => $this->replyEmail->id,
            'from_email' => $this->replyEmail->from_email,
            'original_subject' => $this->originalEmail->subject,
            'reply_subject' => $this->replyEmail->subject,
            'received_at' => $this->replyEmail->received_at,
            'task_id' => $this->task?->id,
            'task_title' => $this->task?->title,
            'reply_preview' => substr(strip_tags($this->replyEmail->body), 0, 100) . '...',
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'email_reply_received';
    }
}
