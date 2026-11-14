<?php

namespace App\Notifications;

use App\Notifications\Channels\TelegramChannel;
use App\Notifications\Support\TelegramMessagePayload;
use App\Services\Telegram\TelegramBotHelpers;
use Carbon\CarbonInterface;
use Illuminate\Notifications\Notification;

/**
 * Повідомляє користувача в Telegram про успішний вхід у застосунок за кодом.
 */
class LoginSuccessNotification extends Notification
{
    public function __construct(
        private readonly string $platform,
        private readonly CarbonInterface $time,
    ) {}

    public function via(mixed $notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram(mixed $notifiable): TelegramMessagePayload
    {
        return new TelegramMessagePayload(
            text: TelegramBotHelpers::renderTemplate('auth_success', [
                // Платформа приходить із заголовка X-Client-Platform — екрануємо для HTML.
                '{platform}' => htmlspecialchars($this->platform, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                '{time}' => $this->time->timezone(config('app.timezone'))->format('d.m.Y H:i'),
            ], '✅ Авторизація успішна'),
        );
    }
}
