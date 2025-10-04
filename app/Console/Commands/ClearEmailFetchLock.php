<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ClearEmailFetchLock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:clear-lock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear any stuck email fetch locks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Clearing email fetch locks...');

        // Clear the old lock key
        $oldLockKey = 'emails:fetch-designers-inbox:running';
        if (Cache::has($oldLockKey)) {
            Cache::forget($oldLockKey);
            $this->info('✅ Old email fetch lock cleared');
        }

        // Clear the new lock key
        $newLockKey = 'auto-email-fetch:running';
        if (Cache::has($newLockKey)) {
            Cache::forget($newLockKey);
            $this->info('✅ Auto email fetch lock cleared');
        }

        // Clear all cache entries related to email fetching
        try {
            $cacheEntries = DB::table('cache')->where('key', 'like', '%email%')
                ->orWhere('key', 'like', '%fetch%')
                ->orWhere('key', 'like', '%running%')
                ->orWhere('key', 'like', '%lock%')
                ->orWhere('key', 'like', '%auto%')
                ->get();

            foreach ($cacheEntries as $entry) {
                Cache::forget($entry->key);
                $this->info("Cleared: {$entry->key}");
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
