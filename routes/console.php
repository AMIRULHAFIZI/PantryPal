<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Auto-delete expired pantry items after a 30-day grace period.
 * To run this locally, execute: php artisan schedule:run
 * To run continuously, set up a Windows Task Scheduler job that runs:
 *   php artisan schedule:run  (every minute)
 */
Schedule::command('pantry:delete-expired')->daily();
