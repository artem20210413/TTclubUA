<?php

use App\Models\PinnedMessage;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

test('the sweep unpins only expired records and deletes them, leaving others untouched', function () {
    $expired = PinnedMessage::create([
        'chat_id' => 'chat-1',
        'message_id' => 'expired-msg',
        'unpin_at' => now()->subMinute(),
    ]);
    $notYetExpired = PinnedMessage::create([
        'chat_id' => 'chat-1',
        'message_id' => 'future-msg',
        'unpin_at' => now()->addHour(),
    ]);
    $indefinite = PinnedMessage::create([
        'chat_id' => 'chat-1',
        'message_id' => 'indefinite-msg',
        'unpin_at' => null,
    ]);

    $api = Mockery::mock(Api::class);
    $api->shouldReceive('unpinChatMessage')
        ->once()
        ->with(['chat_id' => 'chat-1', 'message_id' => 'expired-msg']);

    $this->app->instance(Api::class, $api);

    $this->artisan('tg:unpin-expired-messages')->assertExitCode(0);

    expect(PinnedMessage::whereKey($expired->id)->exists())->toBeFalse();
    expect(PinnedMessage::whereKey($notYetExpired->id)->exists())->toBeTrue();
    expect(PinnedMessage::whereKey($indefinite->id)->exists())->toBeTrue();
});

test('a failure on one record is logged and does not block the rest of the batch', function () {
    $failing = PinnedMessage::create([
        'chat_id' => 'chat-1',
        'message_id' => 'gone-msg',
        'unpin_at' => now()->subMinute(),
    ]);
    $succeeding = PinnedMessage::create([
        'chat_id' => 'chat-1',
        'message_id' => 'ok-msg',
        'unpin_at' => now()->subMinute(),
    ]);

    $api = Mockery::mock(Api::class);
    $api->shouldReceive('unpinChatMessage')
        ->with(['chat_id' => 'chat-1', 'message_id' => 'gone-msg'])
        ->andThrow(new \RuntimeException('message not found'));
    $api->shouldReceive('unpinChatMessage')
        ->with(['chat_id' => 'chat-1', 'message_id' => 'ok-msg']);

    $this->app->instance(Api::class, $api);

    Log::shouldReceive('error')->once();

    $this->artisan('tg:unpin-expired-messages')->assertExitCode(0);

    expect(PinnedMessage::whereKey($failing->id)->exists())->toBeFalse();
    expect(PinnedMessage::whereKey($succeeding->id)->exists())->toBeFalse();
});
