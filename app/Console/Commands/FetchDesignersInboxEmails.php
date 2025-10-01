<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DesignersInboxEmailService;
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
    protected $signature = 'emails:fetch-designers-inbox {--max-results=100 : Maximum number of emails to fetch} {--force : Force fetch even if recently fetched}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically fetch and store emails from designers@orion-contracting.com inbox';

    /**
     * The email service instance.
     *
     * @var DesignersInboxEmailService
     */
    protected $emailService;

    /**
     * Create a new command instance.
     *
     * @param DesignersInboxEmailService $emailService
     */
    public function __construct(DesignersInboxEmailService $emailService)
    {
        parent::__construct();
        $this->emailService = $emailService;
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

            // Get the first manager user to associate emails with
            $manager = User::whereIn('role', ['admin', 'manager'])->first();

            if (!$manager) {
                $this->error('No manager user found. Please ensure at least one manager user exists.');
                Log::error('FetchDesignersInboxEmails: No manager user found');
                return 1;
            }

            $this->info("Using manager: {$manager->name} (ID: {$manager->id})");
            $this->info("Fetching new emails (up to {$maxResults})...");

            // Fetch new emails from designers inbox (incremental)
            $fetchResult = $this->emailService->fetchNewEmails($maxResults);

            if (!$fetchResult['success']) {
                $this->error('Failed to fetch emails from designers inbox');
                $this->error('Errors: ' . implode(', ', $fetchResult['errors'] ?? []));
                Log::error('FetchDesignersInboxEmails: Failed to fetch emails', $fetchResult);
                return 1;
            }

            $this->info("Successfully fetched {$fetchResult['total_fetched']} new emails from inbox");

            // Store emails in database
            $storeResult = $this->emailService->storeEmailsInDatabase($fetchResult['emails'], $manager);

            $this->info("Stored {$storeResult['stored']} new emails in database");

            if ($storeResult['skipped'] > 0) {
                $this->info("Skipped {$storeResult['skipped']} emails (duplicates prevented)");
            }

            if (!empty($storeResult['errors'])) {
                $this->warn('Some errors occurred while storing emails:');
                foreach ($storeResult['errors'] as $error) {
                    $this->warn("  - {$error}");
                }
                Log::warning('FetchDesignersInboxEmails: Errors while storing emails', $storeResult['errors']);
            }

                // Log the fetch operation for tracking
                $this->emailService->logFetchOperation($fetchResult, $storeResult, $fetchResult['total_fetched']);

                $this->info('Email fetch completed successfully!');
                Log::info('FetchDesignersInboxEmails: Successfully completed', [
                    'fetched' => $fetchResult['total_fetched'],
                    'stored' => $storeResult['stored'],
                    'skipped' => $storeResult['skipped']
                ]);

                return 0;

            } finally {
                // Always release the lock
                Cache::forget($lockKey);
            }

        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            Log::error('FetchDesignersInboxEmails: Exception occurred', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Release lock on error
            Cache::forget($lockKey);
            return 1;
        }
    }
}
