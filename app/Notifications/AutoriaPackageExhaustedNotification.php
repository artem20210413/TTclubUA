<?php

namespace App\Notifications;

use App\Notifications\Channels\TelegramChannel;
use App\Notifications\Support\TelegramMessagePayload;
use App\Services\Telegram\TelegramBotHelpers;
use Illuminate\Notifications\Notification;

/**
 * Tells the system-errors chat that AUTO.RIA rejects the car sync with 403 (package exhausted or
 * API key rejected), with a button to renew the package by hand (FR-003, FR-004, FR-008).
 */
class AutoriaPackageExhaustedNotification extends Notification
{
    public const RENEW_URL = 'https://developers.ria.com/payment/';

    public function via(mixed $notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram(mixed $notifiable): TelegramMessagePayload
    {
        return new TelegramMessagePayload(
            text: TelegramBotHelpers::renderTemplate('autoria_package_exhausted.message'),
            buttons: [__('telegram.autoria_package_exhausted.button') => self::RENEW_URL],
        );
    }
}
