<?php

use App\Models\TelegramMessage;
use App\Models\User;
use App\Services\Telegram\Commands\Set\CommandStart;
use App\Services\Telegram\Dto\TelegramMessageDto;
use Telegram\Bot\Api;

test('a failed telegram send during a bot command is caught and logged, not thrown', function () {
    $api = Mockery::mock(Api::class);
    $api->shouldReceive('sendMessage')->once()->andThrow(new \RuntimeException('Telegram API unavailable'));
    $this->app->instance(Api::class, $api);

    $user = User::factory()->create(['telegram_id' => 555]);

    $dto = new TelegramMessageDto([
        'chat' => ['id' => 555, 'type' => 'private'],
        'from' => ['id' => 555, 'first_name' => 'Test'],
        'text' => '/start',
    ]);
    $dto->setUser($user);

    CommandStart::action($dto);

    expect(TelegramMessage::where('chat_id', 555)->where('direction', 'out')->exists())->toBeTrue();
});
