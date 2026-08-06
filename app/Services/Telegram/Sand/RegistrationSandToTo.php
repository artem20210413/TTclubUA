<?php

namespace App\Services\Telegram\Sand;

use App\Enum\EnumTelegramEvents;
use App\Models\Registration;
use App\Notifications\RegistrationNotification;
use App\Notifications\Support\TelegramRecipients;
use Illuminate\Support\Facades\Notification;

class RegistrationSandToTo
{
    public static function send(Registration $registration): void
    {
        $chatIds = EnumTelegramEvents::REGISTRATION->getIds();

        Notification::send(
            TelegramRecipients::routes($chatIds),
            new RegistrationNotification($registration)
        );
    }
}
