<?php

use Illuminate\Console\Scheduling\Schedule;

it('schedules the daily digest command once', function () {
    $events = collect(app(Schedule::class)->events())
        ->filter(fn ($event) => str_contains($event->command ?? '', 'tg:send-daily-digest'));

    expect($events)->toHaveCount(1);
});

it('schedules the telegram messages retention prune', function () {
    $events = collect(app(Schedule::class)->events())
        ->filter(fn ($event) => str_contains($event->command ?? '', 'clear:telegram-messages'));

    expect($events)->toHaveCount(1);
});
