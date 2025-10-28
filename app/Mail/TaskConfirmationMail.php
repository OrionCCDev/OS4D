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
use Illuminate\Support\Facades\Log;

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
        // Process email preparation body to replace signature placeholder
        $processedEmailPreparation = $this->processEmailPreparationBody();

        return new Content(
            view: 'emails.task-confirmation',
            with: [
                'task' => $this->task,
                'emailPreparation' => $processedEmailPreparation,
                'sender' => $this->sender,
                'signature' => $this->signatureService->getSignatureForEmail($this->sender, 'html'),
            ]
        );
    }

    /**
     * Process email preparation body to replace signature placeholder
     */
    private function processEmailPreparationBody()
    {
        $signature = $this->signatureService->getSignatureForEmail($this->sender, 'html');
        $plainTextSignature = $this->signatureService->getSignatureForEmail($this->sender, 'plain');

        // Clone the email preparation to avoid modifying the original
        $processedPreparation = clone $this->emailPreparation;

        // Replace signature placeholder in HTML body
        if ($processedPreparation->body) {
            $processedPreparation->body = str_replace(
                '<!-- Professional Signature will be added here by EmailSignatureService -->',
                $signature,
                $processedPreparation->body
            );
        }

        // Replace signature placeholder in plain text body (if it exists)
        if (property_exists($processedPreparation, 'plain_text_body') && $processedPreparation->plain_text_body) {
            $processedPreparation->plain_text_body = str_replace(
                '<!-- Professional Signature will be added here by EmailSignatureService -->',
                $plainTextSignature,
                $processedPreparation->plain_text_body
            );
        }

        return $processedPreparation;
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // Process email preparation attachments (manually uploaded for this email)
        if ($this->emailPreparation->attachments) {
            \Log::info('TaskConfirmationMail: Processing email preparation attachments - Count: ' . count($this->emailPreparation->attachments));
            foreach ($this->emailPreparation->attachments as $attachmentPath) {
                $fullPath = storage_path('app/' . $attachmentPath);
                \Log::info('TaskConfirmationMail: Checking email preparation attachment: ' . $fullPath . ' - Exists: ' . (file_exists($fullPath) ? 'Yes' : 'No'));
                if (file_exists($fullPath)) {
                    $fileSize = filesize($fullPath);
                    \Log::info('TaskConfirmationMail: Adding email preparation attachment: ' . basename($attachmentPath) . ' - Size: ' . $fileSize . ' bytes');
                    $attachments[] = Attachment::fromStorage($attachmentPath);
                } else {
                    \Log::error('TaskConfirmationMail: Email preparation attachment file not found: ' . $fullPath);
                }
            }
        } else {
            \Log::info('TaskConfirmationMail: No email preparation attachments found');
        }

        // Automatically attach only task attachments marked as required for email
        $requiredTaskAttachments = $this->task->requiredAttachments;
        if ($requiredTaskAttachments && $requiredTaskAttachments->count() > 0) {
            \Log::info('TaskConfirmationMail: Processing required task attachments - Count: ' . $requiredTaskAttachments->count());
            foreach ($requiredTaskAttachments as $taskAttachment) {
                $fullPath = storage_path('app/public/' . $taskAttachment->path);
                \Log::info('TaskConfirmationMail: Checking required task attachment: ' . $fullPath . ' - Exists: ' . (file_exists($fullPath) ? 'Yes' : 'No'));
                if (file_exists($fullPath)) {
                    $fileSize = filesize($fullPath);
                    \Log::info('TaskConfirmationMail: Adding required task attachment: ' . $taskAttachment->original_name . ' - Size: ' . $fileSize . ' bytes');
                    $attachments[] = Attachment::fromStorage('public/' . $taskAttachment->path)
                        ->as($taskAttachment->original_name);
                } else {
                    \Log::error('TaskConfirmationMail: Required task attachment file not found: ' . $fullPath);
                }
            }
        } else {
            \Log::info('TaskConfirmationMail: No required task attachments found');
        }

        \Log::info('TaskConfirmationMail: Total attachments prepared: ' . count($attachments));
        return $attachments;
    }
}
