<?php

use App\Models\User;
use App\Notifications\LoginCodeRevokedNotification;
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

    Notification::assertNothingSent();
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
    Notification::assertNothingSent();
});
