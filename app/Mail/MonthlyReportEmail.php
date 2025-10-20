<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class MonthlyReportEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $emailData;

    /**
     * Create a new message instance.
     */
    public function __construct(array $emailData)
    {
        $this->emailData = $emailData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $monthYear = $this->emailData['monthYear'];
        $userName = $this->emailData['user']->name;

        return new Envelope(
            subject: "📊 Monthly Performance Report - {$monthYear} | {$userName}",
            from: config('mail.from.address'),
            replyTo: config('mail.from.address'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.monthly-report',
            with: $this->emailData,
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        $attachments = [];

        // Add PDF report
        if (isset($this->emailData['pdfPath']) && file_exists($this->emailData['pdfPath'])) {
            $attachments[] = Attachment::fromPath($this->emailData['pdfPath'])
                ->as('Monthly_Report_' . $this->emailData['user']->name . '_' . $this->emailData['monthYear'] . '.pdf')
                ->withMime('application/pdf');
        }

        // Add company logo
        $logoPath = public_path('uploads/logo-blue.webp');
        if (file_exists($logoPath)) {
            $attachments[] = Attachment::fromPath($logoPath)
                ->as('logo.webp')
                ->withMime('image/webp');
        }

        return $attachments;
    }
}
