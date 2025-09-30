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
