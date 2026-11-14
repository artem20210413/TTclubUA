<?php

namespace App\Notifications;

use App\Notifications\Channels\TelegramChannel;
use App\Notifications\Support\TelegramMessagePayload;
use App\Services\Telegram\TelegramBotHelpers;
use Illuminate\Notifications\Notification;

/**
 * Повідомляє користувача, що код входу анульовано через забагато невдалих спроб.
 */
class LoginCodeRevokedNotification extends Notification
{
    public function __construct(private readonly int $attempts) {}

    public function via(mixed $notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram(mixed $notifiable): TelegramMessagePayload
    {
        return new TelegramMessagePayload(
            text: TelegramBotHelpers::renderTemplate('auth_code_revoked', [
                '{attempts}' => $this->attempts,
            ]),
        );
    }
}
