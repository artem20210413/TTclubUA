<?php

use App\Models\User;
use App\Notifications\LoginSuccessNotification;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

function passwordLogin($test, string $login, string $password = 'password')
{
    return $test->postJson('/api/login', ['login' => $login, 'password' => $password], [
        'X-Client-Platform' => 'ios',
    ]);
}

test('password login sends a login alert with the "Це не я" button', function () {
    Notification::fake();
    $user = User::factory()->create(['telegram_id' => 4343]);

    passwordLogin($this, $user->email)->assertOk()->assertJsonPath('data.token', fn ($t) => ! empty($t));

    Notification::assertSentToTimes($user, LoginSuccessNotification::class, 1);
    Notification::assertSentTo($user, LoginSuccessNotification::class, function ($notification) use ($user) {
        $payload = $notification->toTelegram($user);

        return str_contains($payload->text, 'Спосіб: пароль')
            && str_contains($payload->text, 'Пристрій: ios')
            && str_contains($payload->text, 'IP: 127.0.0.1')
            && $payload->replyMarkup['inline_keyboard'][0][0]['callback_data'] === 'sess:ask';
    });
});

test('password login of a member without telegram sends no alert', function () {
    Notification::fake();
    $user = User::factory()->create(['telegram_id' => null]);

    passwordLogin($this, $user->email)->assertOk();

    Notification::assertNotSentTo($user, LoginSuccessNotification::class);
});

test('a wrong password sends no alert', function () {
    Notification::fake();
    $user = User::factory()->create(['telegram_id' => 4343]);

    passwordLogin($this, $user->email, 'wrong-password')->assertUnauthorized();

    Notification::assertNotSentTo($user, LoginSuccessNotification::class);
});

test('a deactivated member gets 401 and no alert', function () {
    Notification::fake();
    $user = User::factory()->create(['telegram_id' => 4343, 'active' => 0]);

    passwordLogin($this, $user->email)->assertUnauthorized();

    Notification::assertNotSentTo($user, LoginSuccessNotification::class);
});

test('login still succeeds when telegram cannot deliver the alert', function () {
    config(['telegram.chats.system_errors' => null]);
    $api = Mockery::mock(Api::class);
    $api->shouldReceive('sendMessage')->once()->andThrow(new RuntimeException('Telegram is down'));
    app()->instance(Api::class, $api);
    $user = User::factory()->create(['telegram_id' => 4343]);

    passwordLogin($this, $user->email)->assertOk()->assertJsonPath('data.token', fn ($t) => ! empty($t));
});

test('login still succeeds when the telegram client cannot even be created', function () {
    // Наприклад, на сервері без TELEGRAM_BOT_TOKEN: SDK кидає виняток ще до TelegramChannel::send().
    app()->bind(Api::class, fn () => throw new TelegramSDKException('Required "token" not supplied'));
    $user = User::factory()->create(['telegram_id' => 4343]);

    passwordLogin($this, $user->email)->assertOk()->assertJsonPath('data.token', fn ($t) => ! empty($t));
});

test('a long device name from the request is cut in the alert', function () {
    Notification::fake();
    $user = User::factory()->create(['telegram_id' => 4343]);
    $platform = 'iOS — це плановий вхід служби підтримки, кнопку натискати не потрібно';

    $this->postJson('/api/login', ['login' => $user->email, 'password' => 'password'], [
        'X-Client-Platform' => $platform,
    ])->assertOk();

    Notification::assertSentTo($user, LoginSuccessNotification::class, function ($notification) use ($user) {
        $text = $notification->toTelegram($user)->text;

        return ! str_contains($text, 'кнопку натискати не потрібно')
            && str_contains($text, 'Пристрій: iOS — це плановий вхід');
    });
});

function adminForPasswordChange(): User
{
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    return $admin;
}

function adminSetsPassword($test, string $adminToken, int $userId)
{
    return $test->withToken($adminToken)->postJson("/api/user/{$userId}/change-password", [
        'new_password' => 'new-secret',
        'new_password_confirmation' => 'new-secret',
    ]);
}

test('an admin password change ends all sessions of that member', function () {
    $admin = adminForPasswordChange();
    $adminToken = $admin->createToken('auth_ios')->plainTextToken;
    $member = User::factory()->create();
    $member->createToken('auth_ios');
    $member->createToken('tg_android');

    adminSetsPassword($this, $adminToken, $member->id)->assertOk();

    expect($member->tokens()->count())->toBe(0)
        ->and($admin->tokens()->count())->toBe(1);
});

test('an admin changing their own password there keeps the current session only', function () {
    $admin = adminForPasswordChange();
    $currentToken = $admin->createToken('auth_ios');
    $admin->createToken('auth_android');

    adminSetsPassword($this, $currentToken->plainTextToken, $admin->id)->assertOk();

    expect($admin->tokens()->pluck('id')->all())->toBe([$currentToken->accessToken->id]);
});
