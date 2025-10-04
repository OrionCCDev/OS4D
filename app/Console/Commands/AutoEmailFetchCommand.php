<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutoEmailFetchService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AutoEmailFetchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:auto-fetch {--interval=5 : Fetch interval in minutes} {--max-results=50 : Maximum number of emails to fetch per run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically fetch and process emails from designers inbox with notifications';

    /**
     * The auto email service instance.
     *
     * @var AutoEmailFetchService
     */
    protected $autoEmailService;

    /**
     * Create a new command instance.
     *
     * @param AutoEmailFetchService $autoEmailService
     */
    public function __construct(AutoEmailFetchService $autoEmailService)
    {
        parent::__construct();
        $this->autoEmailService = $autoEmailService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting automatic email fetch process...');

        try {
            $interval = (int) $this->option('interval');
            $maxResults = (int) $this->option('max-results');

            // Check if another auto-fetch is running (use atomic lock)
            $lockKey = 'auto-email-fetch:running';
            $lockValue = time() . '-' . uniqid();

            // Try to acquire lock atomically
            if (!Cache::add($lockKey, $lockValue, 300)) {
                $this->info('Another auto-fetch process is already running. Skipping...');
                Log::info('AutoEmailFetchCommand: Skipped - another instance is running');
                return 0;
            }

            try {
                $this->info("Fetching emails with max results: {$maxResults}");

                // Perform auto-fetch
                $result = $this->autoEmailService->autoFetchAndProcess();

                if ($result['success']) {
                    $this->info("✅ Auto-fetch completed successfully!");
                    $this->info("   - Fetched: {$result['fetched']} emails");
                    $this->info("   - Stored: {$result['stored']} new emails");
                    $this->info("   - Skipped: {$result['skipped']} duplicates");
                    $this->info("   - Notifications created: {$result['notifications_created']}");

                    if (!empty($result['errors'])) {
                        $this->warn('Some errors occurred:');
                        foreach ($result['errors'] as $error) {
                            $this->warn("   - {$error}");
                        }
                    }

                    Log::info('AutoEmailFetchCommand: Successfully completed', [
                        'fetched' => $result['fetched'],
                        'stored' => $result['stored'],
                        'skipped' => $result['skipped'],
                        'notifications_created' => $result['notifications_created']
                    ]);

                } else {
                    $this->error('❌ Auto-fetch failed!');
                    $this->error('Errors: ' . implode(', ', $result['errors'] ?? []));

                    Log::error('AutoEmailFetchCommand: Failed', [
                        'errors' => $result['errors']
                    ]);

                    return 1;
                }

                return 0;

            } finally {
                // Always release the lock
                Cache::forget($lockKey);
            }

        } catch (\Exception $e) {
            $this->error('❌ An error occurred: ' . $e->getMessage());
            Log::error('AutoEmailFetchCommand: Exception occurred', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Release lock on error
            Cache::forget($lockKey);
            return 1;
        }
    }
}
