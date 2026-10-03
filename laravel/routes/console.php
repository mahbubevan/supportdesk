<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled tasks
|--------------------------------------------------------------------------
*/

// Lets us verify the scheduler container is alive (used by health checks later)
Schedule::call(function () {
    Cache::put('scheduler:last-run', now()->toIso8601String(), 600);
})->everyMinute()->name('scheduler-heartbeat');
