<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DebugEmailLock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:debug-lock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug email fetch lock issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Debugging email fetch locks...');

        // Check specific lock
        $lockKey = 'auto-email-fetch:running';
        $hasLock = Cache::has($lockKey);
        $lockValue = Cache::get($lockKey);

        $this->info("Lock key: {$lockKey}");
        $this->info("Has lock: " . ($hasLock ? 'YES' : 'NO'));
        $this->info("Lock value: " . ($lockValue ?: 'NULL'));

        // Check all cache entries
        $this->info("\n📋 All cache entries:");
        try {
            $cacheEntries = DB::table('cache')->get();
            if ($cacheEntries->isEmpty()) {
                $this->info('No cache entries found');
            } else {
                foreach ($cacheEntries as $entry) {
                    $this->info("- {$entry->key} (expires: {$entry->expiration})");
                }
            }
        } catch (\Exception $e) {
            $this->error('Error reading cache: ' . $e->getMessage());
        }

        // Check for running processes
        $this->info("\n🔄 Running PHP processes:");
        $processes = shell_exec('ps aux | grep php | grep -v grep');
        if ($processes) {
            $this->info($processes);
        } else {
            $this->info('No PHP processes found');
        }

        // Test lock acquisition
        $this->info("\n🧪 Testing lock acquisition:");
        $testLockKey = 'test-lock-' . time();
        $testValue = time() . '-' . uniqid();

        if (Cache::add($testLockKey, $testValue, 60)) {
            $this->info('✅ Lock acquisition test: SUCCESS');
            Cache::forget($testLockKey);
        } else {
            $this->error('❌ Lock acquisition test: FAILED');
        }

        return 0;
    }
}
