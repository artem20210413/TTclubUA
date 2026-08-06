<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Channels\TelegramChannel;
use App\Notifications\Support\TelegramMessagePayload;
use App\Services\Telegram\TelegramBotHelpers;
use Illuminate\Notifications\Notification;

class SuggestionNotification extends Notification
{
    /**
     * @param  string[]  $photoPaths
     */
    public function __construct(
        private readonly User $user,
        private readonly string $description,
        private readonly ?string $environment,
        private readonly array $photoPaths = [],
    ) {}

    public function via(mixed $notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram(mixed $notifiable): TelegramMessagePayload
    {
        $text = TelegramBotHelpers::generationTextSuggestion($this->user, $this->description, $this->environment);

        if (! empty($this->photoPaths)) {
            return new TelegramMessagePayload(text: $text, mediaGroup: $this->photoPaths);
        }

        return new TelegramMessagePayload(text: $text);
    }
}
