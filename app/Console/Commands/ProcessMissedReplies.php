<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Email;
use App\Services\DesignersInboxEmailService;
use Illuminate\Support\Facades\Log;

class ProcessMissedReplies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:process-missed-replies {--days=7 : Number of days to look back}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process missed replies from designers inbox';

    protected $emailService;

    /**
     * Create a new command instance.
     */
    public function __construct(DesignersInboxEmailService $emailService)
    {
        parent::__construct();
        $this->emailService = $emailService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $this->info("Processing missed replies from the last {$days} days...");

        try {
            // Find emails that look like replies but haven't been processed as replies
            $potentialReplies = Email::where('email_source', 'designers_inbox')
                ->where('created_at', '>=', now()->subDays($days))
                ->where(function($query) {
                    $query->where('subject', 'like', 'Re:%')
                          ->orWhere('subject', 'like', 'RE:%')
                          ->orWhere('subject', 'like', 'Fwd:%')
                          ->orWhere('subject', 'like', 'FWD:%');
                })
                ->whereNull('reply_to_email_id') // Not yet linked to original email
                ->get();

            $this->info("Found {$potentialReplies->count()} potential replies to process");

            $processed = 0;
            $linked = 0;

            foreach ($potentialReplies as $email) {
                $this->line("Processing: {$email->subject} from {$email->from_email}");

                // Convert to email data format
                $emailData = [
                    'subject' => $email->subject,
                    'from_email' => $email->from_email,
                    'to_email' => $email->to_email,
                    'in_reply_to' => $email->message_id, // This might not be correct, but let's try
                    'message_id' => $email->message_id,
                ];

                // Try to find original email
                $originalEmail = $this->findOriginalEmailForReply($emailData);

                if ($originalEmail) {
                    // Link the reply to the original
                    $email->update([
                        'reply_to_email_id' => $originalEmail->id,
                        'user_id' => $originalEmail->user_id,
                        'task_id' => $originalEmail->task_id,
                    ]);

                    // Mark original as replied
                    $originalEmail->update([
                        'status' => 'replied',
                        'replied_at' => now()
                    ]);

                    $this->info("  ✅ Linked to original email ID: {$originalEmail->id}");
                    $linked++;
                } else {
                    $this->warn("  ❌ Could not find original email");
                }

                $processed++;
            }

            $this->info("Processing completed!");
            $this->info("Processed: {$processed} emails");
            $this->info("Linked: {$linked} replies");

        } catch (\Exception $e) {
            $this->error('Error processing missed replies: ' . $e->getMessage());
            Log::error('ProcessMissedReplies error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Find original email this is replying to (copied from service)
     */
    protected function findOriginalEmailForReply(array $emailData): ?Email
    {
        try {
            // Method 1: Try to find by in_reply_to header
            if (!empty($emailData['in_reply_to'])) {
                $originalEmail = Email::where('message_id', $emailData['in_reply_to'])
                    ->where('email_source', 'designers_inbox')
                    ->first();
                if ($originalEmail) {
                    return $originalEmail;
                }
            }

            // Method 2: Try to find by subject (remove "Re:" prefix)
            $cleanSubject = preg_replace('/^(Re:|RE:|Fwd:|FWD:)\s*/i', '', $emailData['subject']);
            $originalEmail = Email::where('subject', $cleanSubject)
                ->where('email_source', 'designers_inbox')
                ->where('email_type', 'sent')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($originalEmail) {
                return $originalEmail;
            }

            // Method 3: Try to find by from_email and similar subject
            if (!empty($emailData['from_email'])) {
                $originalEmail = Email::where('to_email', $emailData['from_email'])
                    ->where('email_source', 'designers_inbox')
                    ->where('email_type', 'sent')
                    ->where('subject', 'like', '%' . $cleanSubject . '%')
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($originalEmail) {
                    return $originalEmail;
                }
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Error finding original email: ' . $e->getMessage());
            return null;
        }
    }
}
