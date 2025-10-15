<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DesignersInboxEmailService;
use App\Services\NotificationService;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class NewEmailFetch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:new-fetch {--max-results=50 : Maximum number of emails to fetch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'New email fetch command with proper atomic locking';

    protected $emailService;
    protected $notificationService;

    public function __construct(
        DesignersInboxEmailService $emailService,
        NotificationService $notificationService
    ) {
        parent::__construct();
        $this->emailService = $emailService;
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting NEW email fetch process...');

        $lockKey = 'new-email-fetch:running';

        try {
            $maxResults = (int) $this->option('max-results');

            // Check for existing lock
            $existingLock = Cache::get($lockKey);
            if ($existingLock) {
                // Check if lock is stale (older than 2 minutes)
                $lockTime = explode('-', $existingLock)[0] ?? 0;
                $currentTime = time();

                if ($currentTime - $lockTime > 120) {
                    // Lock is stale, force clear it
                    Cache::forget($lockKey);
                    $this->info('⚠️  Cleared stale lock');
                    Log::warning('NewEmailFetch: Cleared stale lock');
                } else {
                    // Lock is fresh, skip this run
                    $this->info('Another email fetch process is already running. Skipping...');
                    Log::info('NewEmailFetch: Skipped - another instance is running');
                    return 0;
                }
            }

            // Try to acquire lock atomically with 2 minute timeout
            $lockValue = time() . '-' . uniqid();
            if (!Cache::add($lockKey, $lockValue, 120)) {
                $this->info('Another email fetch process is already running. Skipping...');
                Log::info('NewEmailFetch: Skipped - failed to acquire lock');
                return 0;
            }

            try {
                // Get manager user
                $manager = User::whereIn('role', ['admin', 'manager'])->first();
                if (!$manager) {
                    $this->error('❌ No manager user found');
                    return 1;
                }

                $this->info("Using manager: {$manager->name} (ID: {$manager->id})");

                // Fetch emails
                $this->info("Fetching emails with max results: {$maxResults}");
                $fetchResult = $this->emailService->fetchNewEmails($maxResults);

                if (!$fetchResult['success']) {
                    $this->error('❌ Failed to fetch emails: ' . implode(', ', $fetchResult['errors']));
                    return 1;
                }

                $this->info("✅ Fetched: {$fetchResult['total_fetched']} emails");

                if ($fetchResult['total_fetched'] === 0) {
                    $this->info('ℹ️  No new emails found');
                    return 0;
                }

                // Store emails
                $storeResult = $this->emailService->storeEmailsInDatabase($fetchResult['emails'], $manager);
                $this->info("✅ Stored: {$storeResult['stored']} new emails");
                $this->info("⏭️  Skipped: {$storeResult['skipped']} duplicates");

                // Create notifications
                $notificationCount = 0;
                if (!empty($storeResult['stored_emails'])) {
                    foreach ($storeResult['stored_emails'] as $email) {
                        try {
                            $this->notificationService->createNewEmailNotification($email);
                            $notificationCount++;
                        } catch (\Exception $e) {
                            $this->warn("Failed to create notification for email {$email->id}: " . $e->getMessage());
                        }
                    }
                }

                $this->info("🔔 Created: {$notificationCount} notifications");

                Log::info('NewEmailFetch: Successfully completed', [
                    'fetched' => $fetchResult['total_fetched'],
                    'stored' => $storeResult['stored'],
                    'skipped' => $storeResult['skipped'],
                    'notifications' => $notificationCount
                ]);

                return 0;

            } finally {
                // Always release the lock
                Cache::forget($lockKey);
                $this->info('🔓 Lock released');
            }

        } catch (\Exception $e) {
            $this->error('❌ Exception: ' . $e->getMessage());
            Log::error('NewEmailFetch: Exception occurred', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Ensure lock is released even on exception
            Cache::forget($lockKey);

            return 1;
        }
    }
}
