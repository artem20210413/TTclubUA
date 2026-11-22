<?php

use App\Models\TelegramMessage;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Message;

const SESSION_OWNER_TG_ID = 5550001;

beforeEach(function () {
    config(['services.telegram.webhook_secret' => null]);

    // Записуємо всі виклики Telegram API, щоб перевіряти їх після запиту.
    $this->tgCalls = [];
    $this->tgApi = Mockery::mock(Api::class);
    $this->tgApi->shouldReceive('answerCallbackQuery')->andReturnUsing(function (array $params) {
        $this->tgCalls[] = ['answerCallbackQuery', $params];

        return true;
    })->byDefault();
    foreach (['editMessageReplyMarkup', 'editMessageText'] as $method) {
        $this->tgApi->shouldReceive($method)->andReturnUsing(function (array $params) use ($method) {
            $this->tgCalls[] = [$method, $params];

            return new Message([]);
        })->byDefault();
    }
    app()->instance(Api::class, $this->tgApi);

    $this->owner = User::factory()->create(['telegram_id' => SESSION_OWNER_TG_ID]);
});

function pressSessionButton($test, int $fromId, string $data)
{
    return $test->postJson('/api/telegram/webhook', [
        'update_id' => 1,
        'callback_query' => [
            'id' => 'cb-1',
            'from' => ['id' => $fromId, 'first_name' => 'U'],
            'data' => $data,
            'message' => [
                'message_id' => 55,
                'chat' => ['id' => $fromId, 'type' => 'private'],
            ],
        ],
    ]);
}

function telegramCallsOf($test, string $method): array
{
    return array_values(array_map(
        fn (array $call) => $call[1],
        array_filter($test->tgCalls, fn (array $call) => $call[0] === $method),
    ));
}

function callbackDataOf(array $params): array
{
    $markup = json_decode($params['reply_markup'], true);

    return collect($markup['inline_keyboard'])->flatten(1)->pluck('callback_data')->all();
}

test('"Це не я" swaps the button for the yes/no confirmation and keeps the sessions', function () {
    $this->owner->createToken('auth_ios');

    pressSessionButton($this, SESSION_OWNER_TG_ID, 'sess:ask')->assertOk();

    $edits = telegramCallsOf($this, 'editMessageReplyMarkup');
    expect($edits)->toHaveCount(1)
        ->and($edits[0]['chat_id'])->toBe(SESSION_OWNER_TG_ID)
        ->and($edits[0]['message_id'])->toBe(55)
        ->and(callbackDataOf($edits[0]))->toBe(['sess:yes', 'sess:no'])
        ->and(telegramCallsOf($this, 'editMessageText'))->toBeEmpty();

    $answers = telegramCallsOf($this, 'answerCallbackQuery');
    expect($answers)->toHaveCount(1)
        ->and($answers[0]['callback_query_id'])->toBe('cb-1')
        ->and($answers[0]['text'])->toBe('Завершити всі сесії? Ви вийдете на всіх пристроях.')
        ->and($this->owner->tokens()->count())->toBe(1);
});

test('"Ні" brings the "Це не я" button back and keeps the sessions', function () {
    $this->owner->createToken('auth_ios');

    pressSessionButton($this, SESSION_OWNER_TG_ID, 'sess:no')->assertOk();

    $edits = telegramCallsOf($this, 'editMessageReplyMarkup');
    expect($edits)->toHaveCount(1)
        ->and(callbackDataOf($edits[0]))->toBe(['sess:ask'])
        ->and(telegramCallsOf($this, 'answerCallbackQuery')[0]['text'])->toBe('Скасовано. Сесії не змінено.')
        ->and($this->owner->tokens()->count())->toBe(1);
});

test('"Так" ends all sessions, clears the login code and rewrites the message', function () {
    $plainToken = $this->owner->createToken('auth_ios')->plainTextToken;
    $this->owner->createToken('tg_android');
    $this->owner->generateAndStoreLoginCode();

    Log::spy();
    Log::shouldReceive('channel')->andReturnSelf();

    pressSessionButton($this, SESSION_OWNER_TG_ID, 'sess:yes')->assertOk();

    expect($this->owner->tokens()->count())->toBe(0)
        ->and($this->owner->getLoginCodeFromCache())->toBeNull();

    $texts = telegramCallsOf($this, 'editMessageText');
    expect($texts)->toHaveCount(1)
        ->and($texts[0]['text'])->toContain('Усі сесії завершено (2)')
        ->and($texts[0])->not->toHaveKey('reply_markup')
        ->and(telegramCallsOf($this, 'answerCallbackQuery'))->toHaveCount(1);

    Log::shouldHaveReceived('warning')
        ->withArgs(fn ($message, $context = []) => ($context['tokens_deleted'] ?? null) === 2)
        ->once();

    app('auth')->forgetGuards();
    $this->withToken($plainToken)->getJson('/api/homepage-data')->assertUnauthorized();
});

test('"Так" ends the sessions of every account linked to the same telegram id', function () {
    $this->owner->createToken('auth_ios');
    $twin = User::factory()->create(['telegram_id' => SESSION_OWNER_TG_ID]);
    $twin->createToken('auth_ios');
    $twin->createToken('auth_android');

    pressSessionButton($this, SESSION_OWNER_TG_ID, 'sess:yes')->assertOk();

    expect($this->owner->tokens()->count())->toBe(0)
        ->and($twin->tokens()->count())->toBe(0)
        ->and(telegramCallsOf($this, 'editMessageText')[0]['text'])->toContain('Усі сесії завершено (3)');
});

test('pressing "Так" again reports zero ended sessions', function () {
    $this->owner->createToken('auth_ios');

    pressSessionButton($this, SESSION_OWNER_TG_ID, 'sess:yes')->assertOk();
    pressSessionButton($this, SESSION_OWNER_TG_ID, 'sess:yes')->assertOk();

    $texts = telegramCallsOf($this, 'editMessageText');
    expect($texts)->toHaveCount(2)
        ->and($texts[1]['text'])->toContain('Усі сесії завершено (0)');
});

test('a press from a telegram account linked to nobody changes nothing', function () {
    $this->owner->createToken('auth_ios');

    pressSessionButton($this, 9990001, 'sess:yes')->assertOk();

    expect($this->owner->tokens()->count())->toBe(1)
        ->and(telegramCallsOf($this, 'editMessageText'))->toBeEmpty()
        ->and(telegramCallsOf($this, 'editMessageReplyMarkup'))->toBeEmpty()
        ->and(telegramCallsOf($this, 'answerCallbackQuery'))->toHaveCount(1);
});

test('unknown button data changes nothing and is only answered', function () {
    $this->owner->createToken('auth_ios');

    pressSessionButton($this, SESSION_OWNER_TG_ID, 'foo')->assertOk();

    expect($this->owner->tokens()->count())->toBe(1)
        ->and(telegramCallsOf($this, 'editMessageText'))->toBeEmpty()
        ->and(telegramCallsOf($this, 'editMessageReplyMarkup'))->toBeEmpty()
        ->and(telegramCallsOf($this, 'answerCallbackQuery'))->toHaveCount(1);
});

test('a telegram error while editing does not undo the revoke and the press is still answered', function () {
    $this->owner->createToken('auth_ios');
    $this->tgApi->shouldReceive('editMessageText')->andThrow(new RuntimeException('Bad Request: message is not modified'));

    pressSessionButton($this, SESSION_OWNER_TG_ID, 'sess:yes')->assertOk();

    $answers = telegramCallsOf($this, 'answerCallbackQuery');
    expect($this->owner->tokens()->count())->toBe(0)
        ->and($answers)->toHaveCount(1)
        // Повідомлення не оновилося — результат видно хоча б у підказці.
        ->and($answers[0]['text'])->toContain('Усі сесії завершено (1)');
});

test('any other failure while handling a press shows a "try again" notice', function () {
    $this->tgApi->shouldReceive('editMessageReplyMarkup')->andThrow(new RuntimeException('Bad Request'));

    pressSessionButton($this, SESSION_OWNER_TG_ID, 'sess:ask')->assertOk();

    expect(telegramCallsOf($this, 'answerCallbackQuery')[0]['text'])->toBe('Не вдалося виконати дію. Спробуйте ще раз.');
});

test('another linked member pressing "Так" on the owner\'s alert ends only their own sessions', function () {
    $this->owner->createToken('auth_ios');
    $other = User::factory()->create(['telegram_id' => 5550002]);
    $other->createToken('auth_ios');

    // Повідомлення власника (chat.id = власник), але натискає інший член клубу.
    $this->postJson('/api/telegram/webhook', [
        'update_id' => 2,
        'callback_query' => [
            'id' => 'cb-2',
            'from' => ['id' => 5550002, 'first_name' => 'B'],
            'data' => 'sess:yes',
            'message' => ['message_id' => 55, 'chat' => ['id' => SESSION_OWNER_TG_ID, 'type' => 'private']],
        ],
    ])->assertOk();

    expect($this->owner->tokens()->count())->toBe(1)
        ->and($other->tokens()->count())->toBe(0);
});

test('a telegram error while answering the press does not break the webhook', function () {
    $this->tgApi->shouldReceive('answerCallbackQuery')->andThrow(new RuntimeException('query is too old'));

    pressSessionButton($this, SESSION_OWNER_TG_ID, 'sess:ask')->assertOk();
});

test('button presses are not stored as chat messages', function () {
    pressSessionButton($this, SESSION_OWNER_TG_ID, 'sess:ask')->assertOk();

    expect(TelegramMessage::query()->count())->toBe(0);
});

test('a deactivated member can still end their own sessions', function () {
    $this->owner->forceFill(['active' => 0])->save();
    $this->owner->createToken('auth_ios');

    pressSessionButton($this, SESSION_OWNER_TG_ID, 'sess:yes')->assertOk();

    expect($this->owner->tokens()->count())->toBe(0);
});
