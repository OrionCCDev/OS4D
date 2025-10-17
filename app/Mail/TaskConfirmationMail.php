<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\TaskEmailPreparation;
use App\Services\EmailSignatureService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class TaskConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $task;
    public $emailPreparation;
    public $sender;
    public $signatureService;

    /**
     * Create a new message instance.
     */
    public function __construct(Task $task, TaskEmailPreparation $emailPreparation, $sender)
    {
        $this->task = $task;
        $this->emailPreparation = $emailPreparation;
        $this->sender = $sender;
        $this->signatureService = app(EmailSignatureService::class);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailPreparation->subject ?: 'Project Update: Task Completed - ' . $this->task->title,
            from: $this->sender->email,
            replyTo: $this->sender->email,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.task-confirmation',
            with: [
                'task' => $this->task,
                'emailPreparation' => $this->emailPreparation,
                'sender' => $this->sender,
                'signature' => $this->signatureService->getSignatureForEmail($this->sender, 'html'),
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
        $attachments = [];

        if ($this->emailPreparation->attachments) {
            \Log::info('TaskConfirmationMail: Processing attachments - Count: ' . count($this->emailPreparation->attachments));
            foreach ($this->emailPreparation->attachments as $attachmentPath) {
                $fullPath = storage_path('app/' . $attachmentPath);
                \Log::info('TaskConfirmationMail: Checking attachment: ' . $fullPath . ' - Exists: ' . (file_exists($fullPath) ? 'Yes' : 'No'));
                if (file_exists($fullPath)) {
                    $fileSize = filesize($fullPath);
                    \Log::info('TaskConfirmationMail: Adding attachment: ' . basename($attachmentPath) . ' - Size: ' . $fileSize . ' bytes');
                    $attachments[] = Attachment::fromStorage($attachmentPath);
                } else {
                    \Log::error('TaskConfirmationMail: Attachment file not found: ' . $fullPath);
                }
            }
        } else {
            \Log::info('TaskConfirmationMail: No attachments found in email preparation');
        }

        \Log::info('TaskConfirmationMail: Total attachments prepared: ' . count($attachments));
        return $attachments;
    }
}
