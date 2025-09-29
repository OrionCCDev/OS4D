<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SimpleEmailTrackingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckSimpleEmailReplies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:check-simple-replies {--user= : Check replies for specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for email replies by monitoring designers@orion-contracting.com inbox';

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
        $this->info('Starting simple email reply check...');

        try {
            $result = $this->emailTrackingService->checkForReplies();

            if ($result['success']) {
                $replyCount = count($result['replies']);
                $this->info("Found {$replyCount} replies");

                if ($replyCount > 0) {
                    foreach ($result['replies'] as $reply) {
                        $this->line("Reply from: {$reply['from']} - Subject: {$reply['subject']}");
                    }
                }
            } else {
                $this->error('Error checking replies: ' . $result['message']);
                return 1;
            }

            $this->info('Simple email reply check completed successfully');
            return 0;

        } catch (\Exception $e) {
            $this->error('Exception during reply check: ' . $e->getMessage());
            Log::error('Simple email reply check failed: ' . $e->getMessage());
            return 1;
        }
    }
}
