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
        $text = TelegramBotHelpers::renderTemplate('fa_fa', [
            '{owner}' => TelegramBotHelpers::TryMentionPerson($this->owner),
            '{employee}' => TelegramBotHelpers::TryMentionPerson($this->car?->user),
        ]);

        if ($this->time) {
            $text .= "\n".$this->time->toDateTimeString();
        }
        if ($this->description) {
            $text .= "\n\n✍️: {$this->description}";
        }

        if ($this->imagePath) {
            return new TelegramMessagePayload(text: $text, photo: $this->imagePath);
        }

        return new TelegramMessagePayload(text: $text);
    }
}
