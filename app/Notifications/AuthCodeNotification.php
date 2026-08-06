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
        return new TelegramMessagePayload(
            text: TelegramBotHelpers::generationTextAuthCode($this->code, $this->minutes),
            buttons: ['Підтвердити вхід' => $this->universalLink],
        );
    }
}
