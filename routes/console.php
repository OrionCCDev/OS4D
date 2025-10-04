<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// MAIN EMAIL FETCH COMMAND - This handles everything
// Fetches new emails, creates notifications, and processes everything
Schedule::command('emails:auto-fetch --max-results=50')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// DISABLED: All other email commands to prevent conflicts
// The main command above handles all email processing
//
// Previously disabled commands that were causing lock conflicts:
// - email:check-replies (every 5 minutes)
// - email:monitor-replies (every 5 minutes)
// - email:check-simple-replies (every 10 minutes)
// - email:detect-sent (every 2 minutes)
//
// All email processing is now handled by the single emails:auto-fetch command
// which includes fetching, storing, and creating notifications for new emails.
