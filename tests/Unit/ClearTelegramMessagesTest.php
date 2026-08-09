<?php

use App\Models\TelegramMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('deletes telegram messages older than N days and keeps recent ones', function () {
    $old = TelegramMessage::create([
        'chat_id' => '1', 'direction' => 'in', 'text' => 'old', 'message_id' => '1',
    ]);
    $old->forceFill(['created_at' => now()->subDays(40)])->save();

    TelegramMessage::create([
        'chat_id' => '1', 'direction' => 'in', 'text' => 'fresh', 'message_id' => '2',
    ]);

    $this->artisan('clear:telegram-messages', ['days' => 30])->assertSuccessful();

    expect(TelegramMessage::count())->toBe(1)
        ->and(TelegramMessage::first()->text)->toBe('fresh');
});
