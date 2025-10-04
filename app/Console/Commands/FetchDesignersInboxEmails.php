<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DesignersInboxEmailService;
use App\Services\NotificationService;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FetchDesignersInboxEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:fetch-designers-inbox {--max-results=100 : Maximum number of emails to fetch} {--force : Force fetch even if recently fetched} {--no-lock : Skip lock mechanism for debugging}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically fetch and store emails from engineering@orion-contracting.com inbox';

    /**
     * The email service instance.
     *
     * @var DesignersInboxEmailService
     */
    protected $emailService;

    /**
     * The notification service instance.
     *
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * Create a new command instance.
     *
     * @param DesignersInboxEmailService $emailService
     * @param NotificationService $notificationService
     */
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
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting automatic email fetch from designers inbox...');

        try {
            $maxResults = (int) $this->option('max-results');
            $force = $this->option('force');
            $noLock = $this->option('no-lock');

            if ($noLock) {
                $this->info('🚀 Running without lock mechanism (debug mode)');
                return $this->fetchEmailsWithoutLock($maxResults);
            }

            // Check if another instance is already running (lock mechanism)
            $lockKey = 'emails:fetch-designers-inbox:running';
            if (Cache::has($lockKey)) {
                $this->info('Another email fetch process is already running. Skipping...');
                Log::info('FetchDesignersInboxEmails: Skipped - another instance is running');
                return 0;
            }

            // Set lock for 10 minutes
            Cache::put($lockKey, true, 600);

            try {
                // Check if we recently fetched emails (conflict prevention)
                if (!$force) {
                    $lastFetch = \App\Models\EmailFetchLog::getLatestForSource('designers_inbox');
                    if ($lastFetch && $lastFetch->last_fetch_at) {
                        $minutesSinceLastFetch = $lastFetch->last_fetch_at->diffInMinutes(now());
                        if ($minutesSinceLastFetch < 3) { // Less than 3 minutes ago
                            $this->info("Skipping fetch - last fetch was {$minutesSinceLastFetch} minutes ago (too recent)");
                            Log::info("FetchDesignersInboxEmails: Skipped due to recent fetch ({$minutesSinceLastFetch} minutes ago)");
                            return 0;
                        }
                    }
                }

                return $this->fetchEmailsWithLock($maxResults);

            } finally {
                // Always release the lock
                Cache::forget($lockKey);
            }

        } catch (\Exception $e) {
            $this->error('❌ An error occurred: ' . $e->getMessage());
            Log::error('FetchDesignersInboxEmails: Exception occurred', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    protected function fetchEmailsWithoutLock(int $maxResults): int
    {
        try {
            // Get the first manager user to associate emails with
            $manager = User::whereIn('role', ['admin', 'manager'])->first();

            if (!$manager) {
                $this->error('No manager user found. Please ensure at least one manager user exists.');
                Log::error('FetchDesignersInboxEmails: No manager user found');
                return 1;
            }

            $this->info("Using manager: {$manager->name} (ID: {$manager->id})");

            // Fetch emails
            $this->info("Fetching emails with max results: {$maxResults}");
            $fetchResult = $this->emailService->fetchNewEmails($maxResults);

            if (!$fetchResult['success']) {
                $this->error('Failed to fetch emails: ' . implode(', ', $fetchResult['errors']));
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

            Log::info('FetchDesignersInboxEmails (no-lock): Successfully completed', [
                'fetched' => $fetchResult['total_fetched'],
                'stored' => $storeResult['stored'],
                'skipped' => $storeResult['skipped'],
                'notifications' => $notificationCount
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Exception: ' . $e->getMessage());
            Log::error('FetchDesignersInboxEmails (no-lock): Exception occurred', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    protected function fetchEmailsWithLock(int $maxResults): int
    {
        // Get the first manager user to associate emails with
        $manager = User::whereIn('role', ['admin', 'manager'])->first();

        if (!$manager) {
            $this->error('No manager user found. Please ensure at least one manager user exists.');
            Log::error('FetchDesignersInboxEmails: No manager user found');
            return 1;
        }

        $this->info("Using manager: {$manager->name} (ID: {$manager->id})");

        // Fetch emails
        $this->info("Fetching emails with max results: {$maxResults}");
        $fetchResult = $this->emailService->fetchNewEmails($maxResults);

        if (!$fetchResult['success']) {
            $this->error('Failed to fetch emails: ' . implode(', ', $fetchResult['errors']));
            return 1;
        }

        $this->info("✅ Fetched: {$fetchResult['total_fetched']} emails");

        if ($fetchResult['total_fetched'] === 0) {
            $this->info('ℹ️  No new emails found');
            return 0;
        }

        // Store emails in database
        $storeResult = $this->emailService->storeEmailsInDatabase($fetchResult['emails'], $manager);
        $this->info("✅ Stored: {$storeResult['stored']} new emails");
        $this->info("⏭️  Skipped: {$storeResult['skipped']} duplicates");

        // Create notifications for stored emails
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

        // Log the operation
        $this->emailService->logFetchOperation($fetchResult, $storeResult, $fetchResult['current_message_count'] ?? $fetchResult['total_fetched']);

        Log::info('FetchDesignersInboxEmails: Successfully completed', [
            'fetched' => $fetchResult['total_fetched'],
            'stored' => $storeResult['stored'],
            'skipped' => $storeResult['skipped'],
            'notifications' => $notificationCount
        ]);

        return 0;
    }
}
