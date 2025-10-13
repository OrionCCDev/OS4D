<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// QUEUE WORKER - Process background jobs (email sending, etc.)
// This command runs every minute and processes queued jobs
Schedule::command('queue:work --stop-when-empty --max-time=50')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// NEW EMAIL FETCH COMMAND - This handles everything with proper atomic locking
// Uses a completely new lock key to avoid conflicts with old code
Schedule::command('emails:new-fetch --max-results=50')
    ->everyMinute()  // Changed from everyFiveMinutes to everyMinute for faster notifications
    ->withoutOverlapping()
    ->runInBackground();

// DISABLED: All other email commands to prevent conflicts
// The new command above handles all email processing
//
// Previously disabled commands that were causing lock conflicts:
// - email:check-replies (every 5 minutes)
// - email:monitor-replies (every 5 minutes)
// - email:check-simple-replies (every 10 minutes)
// - email:detect-sent (every 2 minutes)
// - emails:auto-fetch (every 5 minutes) - OLD COMMAND WITH LOCK ISSUES
//
// All email processing is now handled by the single emails:new-fetch command
// which includes fetching, storing, and creating notifications for new emails.

// LOG CLEANUP - Clean up and rotate Laravel logs daily at 2 AM
// This prevents log files from growing too large and causing server issues
Schedule::command('logs:cleanup')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground();
