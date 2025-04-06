<?php

namespace App\Services\Telegram;

use App\Models\Car;
use App\Models\User;
use Carbon\Carbon;

class TelegramBotHelpers
{
    public static function MentionPerson(?User $user): string
    {
        $name = $user?->telegram_nickname ?? $user?->name;
        return "<a href='tg://user?id={$user?->telegram_id}'>$name</a>"; // Упоминание
    }

    public static function LinkToPerson(?User $user): string
    {
        return "<a href='https://t.me/{$user?->telegram_nickname}'>$user?->name</a>";
    }

    public static function TryMentionPerson(?User $user): string
    {
        if ($user?->telegram_id)
            return self::MentionPerson($user);
        return self::LinkToPerson($user);

    }

    public static function generationTextMention(User $owner, Car $car, ?string $description, ?Carbon $time = null): string
    {
        $text = "<b>Фа-фа</b>, {employee} - {car}! Лови привітання від {owner}!";

        $text = str_replace("{owner}", self::TryMentionPerson($owner), $text);
        $text = str_replace("{car}", $car->getGeneralShortInfo(), $text);
        $text = str_replace("{employee}", self::TryMentionPerson($car?->user), $text);

        if ($time) {
            $text = $text . "\nДата: " . $time->toDateTimeString();
        }
        if ($description) {
            $text = $text . "\n\nПовідомлення: $description";
        }

        return $text;
    }
}
