<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

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
        $lockKey = 'emails:fetch-designers-inbox:running';

        if (Cache::has($lockKey)) {
            Cache::forget($lockKey);
            $this->info('✅ Email fetch lock cleared successfully');
        } else {
            $this->info('ℹ️  No email fetch lock found');
        }

        return 0;
    }
}
