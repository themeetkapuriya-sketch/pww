<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('backup:monthly')->monthlyOn(1, '00:00');
Schedule::command('attendance:prune --months=6')->monthlyOn(1, '01:00');
