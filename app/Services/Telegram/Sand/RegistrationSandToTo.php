<?php

namespace App\Services\Telegram\Sand;

use App\Enum\EnumTelegramChats;
use App\Enum\EnumTypeMedia;
use App\Models\Registration;
use App\Services\Telegram\TelegramBot;
use App\Services\Telegram\TelegramBotHelpers;

class RegistrationSandToTo
{
    public function __construct(readonly Registration $registration)
    {
//        $text = $this->generationText();
        $text = TelegramBotHelpers::generationTextRegistration($registration);

        $profileImage = $this->registration->getFirstMediaUrl(EnumTypeMedia::PROFILE_PICTURE->value);
        $imageUrls = $this->registration->getMedia(EnumTypeMedia::PHOTO_COLLECTION->value)->map(function ($media) {
            return $media->getUrl();
        })->toArray();

        if ($profileImage != null)
            $imageUrls[] = $profileImage;

        $bot = new TelegramBot(EnumTelegramChats::NOTIFICATION);

        if (empty($imageUrls)) {
            $bot->sendMessage($text);
        } else {
            $bot->sendPhotosWithDescription($imageUrls, $text);
        }
    }

}
