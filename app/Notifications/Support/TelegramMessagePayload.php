<?php

namespace App\Notifications\Support;

class TelegramMessagePayload
{
    /**
     * @param  array<string, string>  $buttons  Inline keyboard as [label => url]
     * @param  string[]  $mediaGroup  Local paths or URLs sent via sendMediaGroup
     * @param  bool  $pin  When true, TelegramChannel dispatches PinTelegramMessageJob for the
     *                     sent message (opt-in per notification; most callers leave this false).
     * @param  ?\Illuminate\Support\Carbon  $pinUntil  When to unpin (null = pin indefinitely).
     */
    public function __construct(
        public readonly ?string $text = null,
        public readonly array $buttons = [],
        public readonly ?string $photo = null,
        public readonly ?string $document = null,
        public readonly array $mediaGroup = [],
        public readonly bool $disableWebPagePreview = false,
        public readonly string $parseMode = 'HTML',
        public readonly ?array $replyMarkup = null,
        public readonly bool $pin = false,
        public readonly ?\Illuminate\Support\Carbon $pinUntil = null,
    ) {}
}
