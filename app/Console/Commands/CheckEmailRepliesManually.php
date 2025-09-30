<?php

namespace App\Console\Commands;

use App\Models\Email;
use App\Models\EmailNotification;
use App\Models\User;
use App\Services\SimpleEmailTrackingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckEmailRepliesManually extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:check-replies-manual {--email= : Check replies for specific email address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually check for email replies by simulating incoming reply data';

    protected $emailTrackingService;

    /**
     * Create a new command instance.
     */
    public function __construct(SimpleEmailTrackingService $emailTrackingService)
    {
        parent::__construct();
        $this->emailTrackingService = $emailTrackingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting manual email reply check...');

        try {
            // Get recent sent emails
            $recentEmails = Email::where('email_type', 'sent')
                ->where('is_tracked', true)
                ->whereNull('replied_at')
                ->where('sent_at', '>=', now()->subDays(7))
                ->orderBy('sent_at', 'desc')
                ->limit(10)
                ->get();

            $this->info('Found ' . $recentEmails->count() . ' recent sent emails to check');

            foreach ($recentEmails as $email) {
                $this->line("Checking email ID: {$email->id} - Subject: {$email->subject}");

                // Simulate a reply for testing
                $this->simulateReplyForEmail($email);
            }

            $this->info('Manual email reply check completed.');

        } catch (\Exception $e) {
            $this->error('Error during manual reply check: ' . $e->getMessage());
            Log::error('Manual email reply check failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Simulate a reply for testing purposes
     */
    protected function simulateReplyForEmail(Email $email): void
    {
        try {
            // Create simulated reply data
            $replyData = [
                'from' => 'test-reply@example.com',
                'to' => $email->from_email,
                'subject' => 'Re: ' . $email->subject,
                'body' => 'This is a simulated reply for testing purposes.',
                'message_id' => 'test-reply-' . time(),
            ];

            // Process the reply
            $success = $this->emailTrackingService->handleIncomingReply($replyData);

            if ($success) {
                $this->info("  ✅ Simulated reply processed successfully for email ID: {$email->id}");
            } else {
                $this->warn("  ⚠️ Failed to process simulated reply for email ID: {$email->id}");
            }

        } catch (\Exception $e) {
            $this->error("  ❌ Error simulating reply for email ID {$email->id}: " . $e->getMessage());
        }
    }
}
