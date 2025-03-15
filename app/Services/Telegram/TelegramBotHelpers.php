<?php

namespace App\Services\Telegram;

use App\Models\Car;
use App\Models\User;

class TelegramBotHelpers
{
    public static function MentionPerson(User $user)
    {

//        $message = "<a href='tg://user?id={$userId}'>@$username</a>"; // Упоминание
    }

    public static function LinkToPerson(?User $user): string
    {
        return "<a href='https://t.me/{$user?->telegram_nickname}'>$user?->name</a>";
    }

    public static function generationTextMention(User $owner, Car $car, ?string $description)
    {
//        $text = "<b> ФА-ФА </b> \nУчасник клубу {owner} знайшов {car}. Власник: {employee}\n";
        $text = "<b>Фа-фа</b>, {employee} - {car}! Лови привітання від {owner}!";

        $text = str_replace("{owner}", self::LinkToPerson($owner), $text);
        $text = str_replace("{car}", $car->getGeneralShortInfo(), $text);
        $text = str_replace("{employee}", self::LinkToPerson($car?->user), $text);

//        dd($description);
        if ($description) {
            $text = $text . "\n\nПовідомлення: $description";
        }

        return $text;
    }
}
