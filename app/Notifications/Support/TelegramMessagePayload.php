<?php

namespace App\Notifications\Support;

class TelegramMessagePayload
{
    /**
     * @param  array<string, string>  $buttons  Inline keyboard as [label => url]
     * @param  string[]  $mediaGroup  Local paths or URLs sent via sendMediaGroup
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
    ) {}
}
