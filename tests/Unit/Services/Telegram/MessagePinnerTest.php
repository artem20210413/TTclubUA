<?php

use App\Models\PinnedMessage;
use App\Services\Telegram\MessagePinner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('pinUntil calls Telegram and stores the given unpin_at', function () {
    $api = Mockery::mock(Api::class);
    $api->shouldReceive('pinChatMessage')
        ->once()
        ->with(['chat_id' => 'chat-1', 'message_id' => 'msg-1', 'disable_notification' => false]);

    $this->app->instance(Api::class, $api);

    $unpinAt = now()->addHours(2);
    app(MessagePinner::class)->pinUntil('chat-1', 'msg-1', $unpinAt);

    $record = PinnedMessage::sole();
    expect($record->chat_id)->toBe('chat-1');
    expect($record->message_id)->toBe('msg-1');
    expect($record->unpin_at->diffInSeconds($unpinAt))->toBeLessThan(1);
    expect($record->delete_after_unpin)->toBeFalse();
});

test('pinUntil with a null unpin_at pins indefinitely', function () {
    $api = Mockery::mock(Api::class);
    $api->shouldReceive('pinChatMessage')->once();

    $this->app->instance(Api::class, $api);

    app(MessagePinner::class)->pinUntil('chat-1', 'msg-1', null);

    expect(PinnedMessage::sole()->unpin_at)->toBeNull();
});

test('pinUntil with deleteAfterUnpin stores the flag for the sweep to use', function () {
    $api = Mockery::mock(Api::class);
    $api->shouldReceive('pinChatMessage')->once();

    $this->app->instance(Api::class, $api);

    app(MessagePinner::class)->pinUntil('chat-1', 'msg-1', now()->addHour(), deleteAfterUnpin: true);

    expect(PinnedMessage::sole()->delete_after_unpin)->toBeTrue();
});

test('pinUntil with notify: false pins silently', function () {
    $api = Mockery::mock(Api::class);
    $api->shouldReceive('pinChatMessage')
        ->once()
        ->with(['chat_id' => 'chat-1', 'message_id' => 'msg-1', 'disable_notification' => true]);

    $this->app->instance(Api::class, $api);

    app(MessagePinner::class)->pinUntil('chat-1', 'msg-1', now()->addHour(), notify: false);

    expect(PinnedMessage::count())->toBe(1);
});

test('re-pinning the same message updates the existing record instead of duplicating it', function () {
    $api = Mockery::mock(Api::class);
    $api->shouldReceive('pinChatMessage')->twice();

    $this->app->instance(Api::class, $api);

    app(MessagePinner::class)->pinUntil('chat-1', 'msg-1', now()->addHour());
    $secondUnpinAt = now()->addHours(5);
    app(MessagePinner::class)->pinUntil('chat-1', 'msg-1', $secondUnpinAt);

    expect(PinnedMessage::count())->toBe(1);
    $record = PinnedMessage::sole();
    expect($record->unpin_at->diffInSeconds($secondUnpinAt))->toBeLessThan(1);
});

test('a Telegram pin failure is logged and no record is persisted', function () {
    $api = Mockery::mock(Api::class);
    $api->shouldReceive('pinChatMessage')
        ->once()
        ->andThrow(new \RuntimeException('bot lacks permission'));

    $this->app->instance(Api::class, $api);

    Log::shouldReceive('error')->once();

    app(MessagePinner::class)->pinUntil('chat-1', 'msg-1', now()->addHour());

    expect(PinnedMessage::count())->toBe(0);
});
