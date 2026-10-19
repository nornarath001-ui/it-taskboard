<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Optional: only used if you run `php artisan schedule:work`.
// Without it, the first page load each day after 08:00 sends the reminders.
Schedule::command('tasks:remind')->hourly();
