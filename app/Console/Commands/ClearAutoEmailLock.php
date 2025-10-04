<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ClearAutoEmailLock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:clear-auto-lock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear any stuck auto email fetch locks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Clearing auto email fetch locks...');

        // Clear the specific lock
        $lockKey = 'auto-email-fetch:running';
        if (Cache::has($lockKey)) {
            Cache::forget($lockKey);
            $this->info('✅ Auto email fetch lock cleared successfully');
        } else {
            $this->info('ℹ️  No auto email fetch lock found');
        }

        // Clear all cache entries related to email fetching
        try {
            $cacheEntries = DB::table('cache')->where('key', 'like', '%email%')
                ->orWhere('key', 'like', '%fetch%')
                ->orWhere('key', 'like', '%running%')
                ->orWhere('key', 'like', '%lock%')
                ->get();

            foreach ($cacheEntries as $entry) {
                Cache::forget($entry->key);
                $this->info("Cleared cache entry: {$entry->key}");
            }

            $this->info('✅ All email-related cache entries cleared');
        } catch (\Exception $e) {
            $this->error('Error clearing cache entries: ' . $e->getMessage());
        }

        // Clear all cache
        Cache::flush();
        $this->info('✅ All cache cleared');

        return 0;
    }
}
