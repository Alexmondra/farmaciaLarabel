<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('backup:database-local')->dailyAt(config('backup.schedule.local_database_at'));
Schedule::command('backup:run')->dailyAt(config('backup.schedule.run_at'));
Schedule::command('backup:clean')->dailyAt(config('backup.schedule.clean_at'));
Schedule::command('backup:monitor')->dailyAt(config('backup.schedule.monitor_at'));
