<?php

namespace App\Notifications;

use App\Models\Registration;
use App\Notifications\Channels\TelegramChannel;
use App\Notifications\Support\TelegramMessagePayload;
use App\Services\Telegram\TelegramBotHelpers;
use Illuminate\Notifications\Notification;

class RegistrationNotification extends Notification
{
    public function __construct(private readonly Registration $registration) {}

    public function via(mixed $notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram(mixed $notifiable): TelegramMessagePayload
    {
        $text = TelegramBotHelpers::generationTextRegistration($this->registration);

        $profileImage = $this->registration->getFirstMediaUrl(\App\Enum\EnumTypeMedia::PROFILE_PICTURE->value);
        $imageUrls = $this->registration->getMedia(\App\Enum\EnumTypeMedia::PHOTO_COLLECTION->value)
            ->map(fn ($media) => $media->getUrl())
            ->toArray();

        if ($profileImage) {
            $imageUrls[] = $profileImage;
        }

        if (empty($imageUrls)) {
            return new TelegramMessagePayload(text: $text);
        }

        return new TelegramMessagePayload(text: $text, mediaGroup: $imageUrls);
    }
}
