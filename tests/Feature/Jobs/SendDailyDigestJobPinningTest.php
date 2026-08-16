<?php

use App\Jobs\SendDailyDigestJob;
use App\Models\PinnedMessage;
use App\Services\Digest\Contracts\DigestSummarizer;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Message;

const DIGEST_CHAT = '111222';

beforeEach(function () {
    config()->set('telegram.chats.tt_club_ua', DIGEST_CHAT);
    config()->set('telegram.chats.test_bot_2', DIGEST_CHAT);

    app()->bind(DigestSummarizer::class, fn () => new class implements DigestSummarizer
    {
        public function summarize(array $messages): string
        {
            return 'Підсумок дня';
        }
    });
});

function mockTelegramSendReturning(int $messageId): Api
{
    $api = Mockery::mock(Api::class);
    $api->shouldReceive('sendMessage')
        ->once()
        ->andReturn(new Message(['message_id' => $messageId, 'chat' => ['id' => DIGEST_CHAT]]));
    $api->shouldReceive('pinChatMessage')
        ->once()
        ->with(['chat_id' => DIGEST_CHAT, 'message_id' => $messageId]);

    return $api;
}

test('the digest message is pinned for 24 hours after posting', function () {
    $api = mockTelegramSendReturning(4242);
    app()->instance(Api::class, $api);

    $this->travelTo(now()->setTime(12, 0));

    SendDailyDigestJob::dispatchSync();

    $record = PinnedMessage::where('chat_id', DIGEST_CHAT)->where('message_id', '4242')->sole();
    expect($record->unpin_at)->not->toBeNull();
    expect($record->unpin_at->diffInMinutes(now()->addHours(24)))->toBeLessThan(1);
});

test('a digest posted late in the day is still pinned for a full 24 hours', function () {
    $api = mockTelegramSendReturning(9999);
    app()->instance(Api::class, $api);

    $this->travelTo(now()->setTime(23, 50));

    SendDailyDigestJob::dispatchSync();

    $record = PinnedMessage::where('chat_id', DIGEST_CHAT)->where('message_id', '9999')->sole();
    expect($record->unpin_at)->not->toBeNull();
    expect($record->unpin_at->diffInMinutes(now()->addHours(24)))->toBeLessThan(1);
});
