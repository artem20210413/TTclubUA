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
        $text = TelegramBotHelpers::renderTemplate('new_suggestion', [
            '{user}' => TelegramBotHelpers::TryMentionPerson($this->user),
            '{phone}' => $this->user->phone,
            '{description}' => $this->description,
            '{environment_line}' => $this->environment ?? '-',
        ]);

        if (! empty($this->photoPaths)) {
            return new TelegramMessagePayload(text: $text, mediaGroup: $this->photoPaths);
        }

        return new TelegramMessagePayload(text: $text);
    }
}
