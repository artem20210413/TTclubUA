<?php

use App\Models\TelegramMessage;
use App\Services\Digest\DailyDigestCollector;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('telegram.digest.source_chats', ['555']);
});

function makeMessage(array $attributes): void
{
    TelegramMessage::create(array_merge([
        'chat_id' => '555',
        'direction' => 'in',
        'text' => 'msg',
        'message_id' => (string) random_int(1, 999999),
    ], $attributes));
}

it('collects today incoming messages for source chats only', function () {
    makeMessage(['text' => 'keep me']);
    makeMessage(['chat_id' => '999', 'text' => 'other chat']);      // wrong chat
    makeMessage(['direction' => 'out', 'text' => 'bot reply']);     // wrong direction
    makeMessage(['text' => null]);                                  // empty

    $result = (new DailyDigestCollector)->forDate(now());

    expect($result)->toBe(['keep me']);
});

it('dedupes repeated message text', function () {
    makeMessage(['text' => 'Привіт']);
    makeMessage(['text' => 'привіт']); // case-insensitive dup
    makeMessage(['text' => 'Інше']);

    $result = (new DailyDigestCollector)->forDate(now());

    expect($result)->toHaveCount(2);
});

it('excludes messages from other days', function () {
    $old = TelegramMessage::create([
        'chat_id' => '555', 'direction' => 'in', 'text' => 'yesterday', 'message_id' => '1',
    ]);
    $old->forceFill(['created_at' => now()->subDays(2)])->save();

    $result = (new DailyDigestCollector)->forDate(now());

    expect($result)->toBe([]);
});
