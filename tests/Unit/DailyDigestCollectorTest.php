<?php

use App\Models\TelegramMessage;
use App\Services\Digest\DailyDigestCollector;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('telegram.chats.tt_club_ua', '555'); // DAILY_DIGEST_COLLECT source
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

it('prefixes messages with the author @username when present', function () {
    makeMessage([
        'text' => 'Продам ракетку',
        'raw' => ['message' => ['from' => ['username' => 'petro']]],
    ]);
    makeMessage(['text' => 'Без ніка', 'raw' => null]);

    $result = (new DailyDigestCollector)->forDate(now());

    expect($result)->toBe(['@petro: Продам ракетку', 'Без ніка']);
});

it('adds reply context from raw so the thread is clear', function () {
    makeMessage([
        'text' => 'Так, беру',
        'raw' => ['message' => [
            'from' => ['username' => 'petro'],
            'reply_to_message' => ['text' => 'Продам ракетку за 500', 'from' => ['username' => 'ivan']],
        ]],
    ]);

    $result = (new DailyDigestCollector)->forDate(now());

    expect($result[0])->toContain('@petro: Так, беру')
        ->and($result[0])->toContain('↩ у відповідь @ivan: "Продам ракетку за 500"');
});

it('appends a jump link for supergroup messages', function () {
    config()->set('telegram.chats.tt_club_ua', '-1001330280439');
    TelegramMessage::create([
        'chat_id' => '-1001330280439',
        'direction' => 'in',
        'text' => 'Продам ракетку',
        'message_id' => '4567',
    ]);

    $result = (new DailyDigestCollector)->forDate(now());

    expect($result[0])->toContain('🔗https://t.me/c/1330280439/4567');
});

it('excludes messages from other days', function () {
    $old = TelegramMessage::create([
        'chat_id' => '555', 'direction' => 'in', 'text' => 'yesterday', 'message_id' => '1',
    ]);
    $old->forceFill(['created_at' => now()->subDays(2)])->save();

    $result = (new DailyDigestCollector)->forDate(now());

    expect($result)->toBe([]);
});
