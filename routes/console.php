<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

app(Schedule::class)->command('tg:sending-list-of-birthdays 8')->weeklyOn(0, '15:00');
app(Schedule::class)->command('tg:sending-list-of-birthdays 0')->dailyAt('09:00');
app(Schedule::class)->command('clear:mention 30')->dailyAt('03:00');
app(Schedule::class)->command('clear:registration 30')->dailyAt('03:05');
//app(Schedule::class)->command('tg:sending-list-of-birthdays 8')->everyMinute();

//test
