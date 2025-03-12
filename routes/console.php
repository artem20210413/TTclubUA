<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

app(Schedule::class)->command('tg:sending-list-of-birthdays 8')->weeklyOn(0, '15:00');
//app(Schedule::class)->command('tg:sending-list-of-birthdays 8')->everyMinute();

//test
