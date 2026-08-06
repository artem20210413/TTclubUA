<?php

namespace App\Notifications;

use App\Notifications\Channels\TelegramChannel;
use App\Notifications\Support\TelegramMessagePayload;
use App\Services\Telegram\TelegramBotHelpers;
use Illuminate\Notifications\Notification;

class AuthCodeNotification extends Notification
{
    public function __construct(
        private readonly string $code,
        private readonly string $universalLink,
        private readonly int $minutes = 10,
    ) {}

    public function via(mixed $notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram(mixed $notifiable): TelegramMessagePayload
    {
        $text = TelegramBotHelpers::renderTemplate('auth_code', [
            '{code}' => $this->code,
            '{minutes}' => $this->minutes,
        ]);

        return new TelegramMessagePayload(
            text: $text,
            buttons: ['Підтвердити вхід' => $this->universalLink],
        );
    }
}
