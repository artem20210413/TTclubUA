<?php

use App\Models\TelegramMessage;
use App\Notifications\AdHocMessageNotification;
use App\Notifications\Support\TelegramMessagePayload;
use App\Notifications\Support\TelegramRecipients;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Telegram\Bot\Api;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('delivery continues to the next recipient after one fails', function () {
    $api = Mockery::mock(Api::class);
    $api->shouldReceive('sendMessage')
        ->twice()
        ->andReturnUsing(function (array $params) {
            if ($params['chat_id'] === 'bad-chat') {
                throw new \RuntimeException('chat not found');
            }

            return [];
        });

    $this->app->instance(Api::class, $api);

    Notification::send(
        TelegramRecipients::routes(['bad-chat', 'good-chat']),
        new AdHocMessageNotification(new TelegramMessagePayload(text: 'hello'))
    );

    expect(TelegramMessage::where('direction', 'out')->count())->toBe(2);
    expect(TelegramMessage::where('chat_id', 'good-chat')->exists())->toBeTrue();
    expect(TelegramMessage::where('chat_id', 'bad-chat')->exists())->toBeTrue();
});

test('a chat id that resolves to nothing is skipped without a log entry', function () {
    $api = Mockery::mock(Api::class);
    $api->shouldNotReceive('sendMessage');

    $this->app->instance(Api::class, $api);

    Notification::send(
        TelegramRecipients::routes([null, '']),
        new AdHocMessageNotification(new TelegramMessagePayload(text: 'hello'))
    );

    expect(TelegramMessage::count())->toBe(0);
});
