<?php

use App\Http\Middleware\ParamsThrottleMiddleware;
use App\Models\User;
use App\Notifications\AuthCodeNotification;
use App\Notifications\LoginCodeRevokedNotification;
use App\Notifications\LoginSuccessNotification;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    // IP-throttle маршруту (5 запитів / 2 хв) перевіряється окремо; тут тестуємо саме ліміт спроб коду.
    $this->withoutMiddleware(ThrottleRequests::class);
    Notification::fake();
    config(['auth.login_code.max_attempts' => 5]);

    $this->user = User::factory()->create([
        'phone' => formatPhoneNumber('+380501112233'),
        'telegram_id' => 4242,
    ]);
    $this->code = $this->user->generateAndStoreLoginCode();
    $this->wrongCode = $this->code === '0000' ? '1111' : '0000';
});

function verifyLoginCode($test, string $code)
{
    return $test->postJson('/api/login/tg/verify', ['phone' => '+380501112233', 'code' => $code]);
}

test('correct code within the limit logs the user in', function () {
    foreach (range(1, 4) as $i) {
        verifyLoginCode($this, $this->wrongCode)->assertStatus(404);
    }

    verifyLoginCode($this, $this->code)->assertOk()->assertJsonPath('data.token', fn ($t) => ! empty($t));

    Notification::assertNotSentTo($this->user, LoginCodeRevokedNotification::class);
});

test('successful login sends a login alert with the "Це не я" button to telegram', function () {
    $this->postJson('/api/login/tg/verify', ['phone' => '+380501112233', 'code' => $this->code], [
        'X-Client-Platform' => '<b>ios</b>',
    ])->assertOk();

    Notification::assertSentTo($this->user, LoginSuccessNotification::class, function ($notification) {
        $payload = $notification->toTelegram($this->user);
        $text = $payload->text;

        return str_contains($text, 'Вхід в акаунт')
            && str_contains($text, 'Спосіб: код Telegram')
            && str_contains($text, 'IP: 127.0.0.1')
            && str_contains($text, '&lt;b&gt;ios&lt;/b&gt;') // платформа з заголовка екранована
            && $payload->replyMarkup['inline_keyboard'][0][0]['callback_data'] === 'sess:ask';
    });
    Notification::assertSentToTimes($this->user, LoginSuccessNotification::class, 1);
});

test('wrong code does not send the success message', function () {
    verifyLoginCode($this, $this->wrongCode)->assertStatus(404);

    Notification::assertNotSentTo($this->user, LoginSuccessNotification::class);
});

test('the attempt after max failures revokes the code and notifies the user in telegram', function () {
    foreach (range(1, 5) as $i) {
        verifyLoginCode($this, $this->wrongCode)->assertStatus(404);
    }

    // 6-та спроба — код анулюється, навіть якщо він правильний.
    verifyLoginCode($this, $this->code)->assertStatus(429);

    expect($this->user->getLoginCodeFromCache())->toBeNull();
    Notification::assertSentTo($this->user, LoginCodeRevokedNotification::class);

    // Код уже недійсний — далі звичайна відповідь, без повторного повідомлення.
    verifyLoginCode($this, $this->code)->assertStatus(404);
    Notification::assertSentToTimes($this->user, LoginCodeRevokedNotification::class, 1);
});

test('the attempt limit comes from config', function () {
    config(['auth.login_code.max_attempts' => 2]);

    verifyLoginCode($this, $this->wrongCode)->assertStatus(404);
    verifyLoginCode($this, $this->wrongCode)->assertStatus(404);
    verifyLoginCode($this, $this->code)->assertStatus(429);

    Notification::assertSentTo($this->user, LoginCodeRevokedNotification::class);
});

test('requesting a new code resets the failed attempts counter', function () {
    foreach (range(1, 5) as $i) {
        verifyLoginCode($this, $this->wrongCode)->assertStatus(404);
    }

    $newCode = $this->user->generateAndStoreLoginCode();

    verifyLoginCode($this, $newCode)->assertOk();
    Notification::assertNotSentTo($this->user, LoginCodeRevokedNotification::class);
});

test('a deactivated member is not sent a login code', function () {
    $this->withoutMiddleware(ParamsThrottleMiddleware::class);
    $this->user->forceFill(['active' => 0])->save();

    $this->postJson('/api/login/tg/send-code', ['phone' => '+380501112233'])
        ->assertStatus(404)
        ->assertJsonPath('message', 'Користувача з таким номером не знайдено.');

    Notification::assertNotSentTo($this->user, AuthCodeNotification::class);
});

test('a member deactivated after getting a code cannot log in with it', function () {
    $this->user->forceFill(['active' => 0])->save();

    verifyLoginCode($this, $this->code)->assertStatus(404);

    expect($this->user->tokens()->count())->toBe(0);
    Notification::assertNotSentTo($this->user, LoginSuccessNotification::class);
});
