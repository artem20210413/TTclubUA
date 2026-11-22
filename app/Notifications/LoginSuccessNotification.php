<?php

namespace App\Notifications;

use App\Notifications\Channels\TelegramChannel;
use App\Notifications\Support\TelegramMessagePayload;
use App\Services\Telegram\TelegramBotHelpers;
use App\Services\Telegram\TelegramCallbackHandler;
use Carbon\CarbonInterface;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Повідомляє користувача в Telegram про кожен успішний вхід у застосунок (за паролем чи кодом)
 * з кнопкою «Це не я — завершити всі сесії» (її обробляє TelegramCallbackHandler).
 */
class LoginSuccessNotification extends Notification
{
    public const METHOD_PASSWORD = 'password';

    public const METHOD_CODE = 'code';

    public const PLATFORM_MAX_LENGTH = 32;

    public function __construct(
        private readonly string $method,
        private readonly string $platform,
        private readonly ?string $ip,
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
                '{method}' => __("telegram.auth_methods.{$this->method}"),
                // Платформа й IP приходять із запиту — екрануємо для HTML. Платформу ще й обрізаємо,
                // щоб той, хто входить, не міг вписати в повідомлення довгий текст «не натискайте кнопку».
                '{platform}' => $this->escape(Str::limit($this->platform, self::PLATFORM_MAX_LENGTH)),
                '{ip}' => $this->ip !== null ? $this->escape($this->ip) : __('telegram.auth_ip_unknown'),
                '{time}' => $this->time->timezone(config('app.timezone'))->format('d.m.Y H:i'),
            ], '✅ Вхід в акаунт'),
            replyMarkup: TelegramCallbackHandler::askKeyboard(),
        );
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
