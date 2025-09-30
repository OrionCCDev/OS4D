<?php

namespace App\Mail;

use App\Models\Email;
use App\Models\Task;
use App\Models\ExternalStakeholder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailReplyStakeholderNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $originalEmail;
    public $replyEmail;
    public $task;
    public $stakeholder;

    /**
     * Create a new message instance.
     */
    public function __construct(Email $originalEmail, Email $replyEmail, Task $task, ExternalStakeholder $stakeholder)
    {
        $this->originalEmail = $originalEmail;
        $this->replyEmail = $replyEmail;
        $this->task = $task;
        $this->stakeholder = $stakeholder;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('📧 Email Reply Update - ' . $this->task->title)
            ->view('emails.email-reply-stakeholder-notification')
            ->with([
                'originalEmail' => $this->originalEmail,
                'replyEmail' => $this->replyEmail,
                'task' => $this->task,
                'stakeholder' => $this->stakeholder,
            ]);
    }
}
