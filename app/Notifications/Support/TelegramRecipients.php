<?php

namespace App\Notifications\Support;

use Illuminate\Support\Facades\Notification;

class TelegramRecipients
{
    /**
     * Maps raw chat IDs (as returned by EnumTelegramEvents::getIds()) to anonymous
     * notification routes, so Notification::send() can iterate/isolate failures per chat.
     *
     * @param  array<int|string|null>  $chatIds
     * @return array<\Illuminate\Notifications\AnonymousNotifiable>
     */
    public static function routes(array $chatIds): array
    {
        return collect($chatIds)
            ->filter()
            ->map(fn ($chatId) => Notification::route('telegram', $chatId))
            ->all();
    }
}
