<?php

namespace App\Notifications;

use App\Notifications\Channels\TelegramChannel;
use App\Notifications\Support\TelegramMessagePayload;
use App\Services\Telegram\TelegramBotHelpers;
use Illuminate\Notifications\Notification;

class CommandReplyNotification extends Notification
{
    /**
     * @param  array<string, mixed>  $replacements  renderTemplate() {token} substitutions
     */
    public function __construct(
        private readonly ?string $templateKey,
        private readonly array $replacements = [],
        private readonly string $default = '---',
        private readonly ?array $replyMarkup = null,
        private readonly string $parseMode = 'HTML',
        private readonly ?string $rawText = null,
    ) {}

    public static function literal(string $text, ?array $replyMarkup = null, string $parseMode = 'HTML'): self
    {
        return new self(templateKey: null, replyMarkup: $replyMarkup, parseMode: $parseMode, rawText: $text);
    }

    public function via(mixed $notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram(mixed $notifiable): TelegramMessagePayload
    {
        $text = $this->templateKey !== null
            ? TelegramBotHelpers::renderTemplate($this->templateKey, $this->replacements, $this->default)
            : $this->rawText;

        return new TelegramMessagePayload(
            text: $text,
            replyMarkup: $this->replyMarkup,
            parseMode: $this->parseMode,
        );
    }
}
