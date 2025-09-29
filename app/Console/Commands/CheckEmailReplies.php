<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\EmailTrackingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckEmailReplies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:check-replies {--user= : Check replies for specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for email replies from Gmail and create notifications';

    protected $emailTrackingService;

    /**
     * Create a new command instance.
     */
    public function __construct(EmailTrackingService $emailTrackingService)
    {
        parent::__construct();
        $this->emailTrackingService = $emailTrackingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting email reply check...');

        $userId = $this->option('user');

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }
            $users = collect([$user]);
        } else {
            // Get all users with Gmail connected
            $users = User::where('gmail_connected', true)->get();
        }

        $totalReplies = 0;
        $processedUsers = 0;

        foreach ($users as $user) {
            $this->info("Checking replies for user: {$user->email} (ID: {$user->id})");

            try {
                $result = $this->emailTrackingService->checkForReplies($user);

                if ($result['success']) {
                    $replies = $result['replies'] ?? [];
                    $totalReplies += count($replies);

                    if (count($replies) > 0) {
                        $this->info("Found " . count($replies) . " new replies for {$user->email}");

                        foreach ($replies as $reply) {
                            $this->line("  - Reply from: {$reply['from']}");
                            $this->line("  - Subject: {$reply['subject']}");
                        }
                    } else {
                        $this->line("  No new replies found");
                    }

                    $processedUsers++;
                } else {
                    $this->error("Error checking replies for {$user->email}: {$result['message']}");
                }
            } catch (\Exception $e) {
                $this->error("Exception checking replies for {$user->email}: " . $e->getMessage());
                Log::error("Email reply check error for user {$user->id}: " . $e->getMessage());
            }
        }

        $this->info("Email reply check completed.");
        $this->info("Processed {$processedUsers} users, found {$totalReplies} new replies.");

        return 0;
    }
}
