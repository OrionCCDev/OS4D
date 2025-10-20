<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// RELIABLE EMAIL MONITOR - More robust email monitoring system
// Uses Laravel Mailbox package with better error handling
Schedule::command('emails:reliable-monitor --max-results=50')
    ->everyFiveMinutes()
    ->runInBackground();

// QUEUE WORKER - Process background jobs (email sending, etc.)
Schedule::command('queue:work --stop-when-empty --max-time=50')
    ->everyMinute()
    ->runInBackground();

// LOG CLEANUP - Clean up and rotate Laravel logs daily at 2 AM
Schedule::command('logs:cleanup')
    ->dailyAt('02:00')
    ->runInBackground();

// MONTHLY REPORTS - Send monthly performance reports to all users on the 1st of every month at 9 AM
Schedule::command('reports:send-monthly')
    ->monthlyOn(1, '09:00')
    ->runInBackground();
