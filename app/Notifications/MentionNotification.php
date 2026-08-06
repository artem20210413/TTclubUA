<?php

namespace App\Notifications;

use App\Models\Car;
use App\Models\User;
use App\Notifications\Channels\TelegramChannel;
use App\Notifications\Support\TelegramMessagePayload;
use App\Services\Telegram\TelegramBotHelpers;
use Carbon\Carbon;
use Illuminate\Notifications\Notification;

class MentionNotification extends Notification
{
    public function __construct(
        private readonly User $owner,
        private readonly Car $car,
        private readonly ?string $description,
        private readonly ?Carbon $time,
        private readonly ?string $imagePath = null,
    ) {}

    public function via(mixed $notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram(mixed $notifiable): TelegramMessagePayload
    {
        $text = TelegramBotHelpers::generationTextMention($this->owner, $this->car, $this->description, $this->time);

        if ($this->imagePath) {
            return new TelegramMessagePayload(text: $text, photo: $this->imagePath);
        }

        return new TelegramMessagePayload(text: $text);
    }
}
