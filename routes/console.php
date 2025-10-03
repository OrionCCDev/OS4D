<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule email reply checking every 5 minutes
Schedule::command('email:check-replies')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule comprehensive email monitoring every 5 minutes
Schedule::command('email:monitor-replies')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule simple email reply checking every 10 minutes
Schedule::command('email:check-simple-replies')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// DISABLED: Old email fetching command to prevent conflicts
// Schedule::command('emails:fetch-designers-inbox')
//     ->everyFiveMinutes()
//     ->withoutOverlapping()
//     ->runInBackground();

// Schedule enhanced auto-email fetch with notifications every 5 minutes
// This is now the PRIMARY command for fetching emails from designers@orion-contracting.com
Schedule::command('emails:auto-fetch --max-results=50')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// DISABLED: These commands were causing conflicts with the main email fetching
// Schedule designers inbox monitoring every 5 minutes
// Schedule::command('email:monitor-designers-inbox')
//     ->everyFiveMinutes()
//     ->withoutOverlapping()
//     ->runInBackground();

// Schedule live email monitoring every 2 minutes
// Schedule::command('email:live-monitor')
//     ->everyTwoMinutes()
//     ->withoutOverlapping()
//     ->runInBackground();

// Schedule sent email detection every 2 minutes (more frequent for better automation)
Schedule::command('email:detect-sent')
    ->everyTwoMinutes()
    ->withoutOverlapping()
    ->runInBackground();
