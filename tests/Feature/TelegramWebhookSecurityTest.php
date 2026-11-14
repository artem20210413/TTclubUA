<?php

use App\Eloquent\UserEloquent;
use App\Http\Controllers\Api\ApiException;
use App\Models\User;
use App\Notifications\NewChatMemberWelcomeNotification;
use App\Services\Telegram\Dto\TelegramMessageDto;
use App\Services\Telegram\Dto\TelegramWebhookDto;
use App\Services\Telegram\TelegramActionPublicHandler;
use Illuminate\Support\Facades\Notification;

const MAIN_CLUB_CHAT_ID = -1001111111111;
const FOREIGN_CHAT_ID = -1009999999999;

beforeEach(function () {
    config(['telegram.chats.tt_club_ua' => (string) MAIN_CLUB_CHAT_ID]);
    Notification::fake();
});

function newMemberWebhook(int $chatId, int $memberTgId): TelegramWebhookDto
{
    return new TelegramWebhookDto([
        'message' => [
            'message_id' => 1,
            'chat' => ['id' => $chatId, 'type' => 'supergroup', 'title' => 'Chat'],
            'from' => ['id' => $memberTgId, 'first_name' => 'Member'],
            'new_chat_members' => [['id' => $memberTgId, 'first_name' => 'Member']],
        ],
    ]);
}

function memberLeftWebhook(int $chatId, int $memberTgId): TelegramWebhookDto
{
    return new TelegramWebhookDto([
        'chat_member' => [
            'chat' => ['id' => $chatId, 'type' => 'supergroup', 'title' => 'Chat'],
            'from' => ['id' => $memberTgId, 'first_name' => 'Member'],
            'old_chat_member' => ['status' => 'member', 'user' => ['id' => $memberTgId, 'first_name' => 'Member']],
            'new_chat_member' => ['status' => 'left', 'user' => ['id' => $memberTgId, 'first_name' => 'Member']],
        ],
    ]);
}

// --- Пункт 1: вступ/вихід лише в головній групі ---

test('joining the main club chat activates the user', function () {
    $user = User::factory()->create(['telegram_id' => 777, 'active' => false]);

    (new TelegramActionPublicHandler(newMemberWebhook(MAIN_CLUB_CHAT_ID, 777)))->handler();

    expect($user->fresh()->active)->toBeTruthy();
});

test('joining a foreign chat does not activate the user', function () {
    $user = User::factory()->create(['telegram_id' => 777, 'active' => false]);

    (new TelegramActionPublicHandler(newMemberWebhook(FOREIGN_CHAT_ID, 777)))->handler();

    expect($user->fresh()->active)->toBeFalsy();
    Notification::assertNothingSent();
});

test('leaving the main club chat deactivates the user', function () {
    $user = User::factory()->create(['telegram_id' => 777, 'active' => true]);

    (new TelegramActionPublicHandler(memberLeftWebhook(MAIN_CLUB_CHAT_ID, 777)))->handler();

    expect($user->fresh()->active)->toBeFalsy();
});

test('leaving a foreign chat does not deactivate the user', function () {
    $user = User::factory()->create(['telegram_id' => 777, 'active' => true]);

    (new TelegramActionPublicHandler(memberLeftWebhook(FOREIGN_CHAT_ID, 777)))->handler();

    expect($user->fresh()->active)->toBeTruthy();
    Notification::assertNothingSent();
});

test('welcome is sent to a whitelisted chat', function () {
    User::factory()->create(['telegram_id' => 777]);

    (new TelegramActionPublicHandler(newMemberWebhook(MAIN_CLUB_CHAT_ID, 777)))->handler();

    Notification::assertSentOnDemand(
        NewChatMemberWelcomeNotification::class,
        fn ($notification, $channels, $notifiable) => (string) $notifiable->routeNotificationFor('telegram') === (string) MAIN_CLUB_CHAT_ID
    );
});

test('welcome is not sent to a chat outside the whitelist', function () {
    (new TelegramActionPublicHandler(newMemberWebhook(FOREIGN_CHAT_ID, 777)))->handler();

    Notification::assertNothingSent();
});

test('nothing is handled when the main chat is not configured', function () {
    config(['telegram.chats.tt_club_ua' => null]);
    $user = User::factory()->create(['telegram_id' => 777, 'active' => true]);

    (new TelegramActionPublicHandler(memberLeftWebhook(MAIN_CLUB_CHAT_ID, 777)))->handler();

    expect($user->fresh()->active)->toBeTruthy();
});

// --- Пункт 3: прив'язка лише власного контакту ---

function contactMessage(int $fromId, ?int $contactUserId, string $phone): TelegramMessageDto
{
    $contact = ['phone_number' => $phone, 'first_name' => 'X'];
    if ($contactUserId !== null) {
        $contact['user_id'] = $contactUserId;
    }

    return new TelegramMessageDto([
        'message_id' => 1,
        'chat' => ['id' => $fromId, 'type' => 'private'],
        'from' => ['id' => $fromId, 'first_name' => 'Sender', 'username' => 'sender_nick'],
        'contact' => $contact,
    ]);
}

test('own contact links the telegram account', function () {
    $user = User::factory()->create(['phone' => '380501112233', 'telegram_id' => null]);

    $linked = UserEloquent::updateByTg(contactMessage(1001, 1001, '+380501112233'));

    expect($linked->id)->toBe($user->id)
        ->and((int) $user->fresh()->telegram_id)->toBe(1001)
        ->and($user->fresh()->telegram_nickname)->toBe('sender_nick');
});

test('someone else\'s contact is rejected and the account is untouched', function () {
    $user = User::factory()->create([
        'phone' => '380501112233',
        'telegram_id' => 2002,
        'telegram_nickname' => 'victim_nick',
    ]);

    expect(fn () => UserEloquent::updateByTg(contactMessage(1001, 2002, '+380501112233')))
        ->toThrow(ApiException::class);

    expect((int) $user->fresh()->telegram_id)->toBe(2002)
        ->and($user->fresh()->telegram_nickname)->toBe('victim_nick');
});

test('a contact without user_id is rejected', function () {
    $user = User::factory()->create(['phone' => '380501112233', 'telegram_id' => 2002]);

    expect(fn () => UserEloquent::updateByTg(contactMessage(1001, null, '+380501112233')))
        ->toThrow(ApiException::class);

    expect((int) $user->fresh()->telegram_id)->toBe(2002);
});
