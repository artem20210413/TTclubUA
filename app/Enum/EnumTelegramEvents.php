<?php

namespace App\Enum;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

enum EnumTelegramEvents
{

//    case NOTIFICATION;
    case FA_FA;
    case LIST_BIRTHDAYS; // Список ДР в очереди
    case TEST;
    case MY;
    case USERS; // определенным пользователям
    case EXPORT_USERS; //експорт всех пользователей
    case REGISTRATION; // при регистрации уведомление

    /**
     * Получить разрешение для качества
     */
    public function getIds(?Collection $users = null): array
    {

        $config = config("telegram.chats");

        $welcome = $config['welcome'] ?? '';
        $testBot2 = $config['test_bot_2'] ?? '';

        $myIds = Auth::user() ? [Auth::user()->telegram_id] : [];
        $usersIds = $users ? $users->pluck('telegram_id')->toArray() : [];

        return match ($this) {
            self::FA_FA => [$welcome],
            self::EXPORT_USERS => [$welcome],
            self::LIST_BIRTHDAYS => [$welcome],
            self::REGISTRATION => [$welcome],

            self::TEST => [$testBot2],

            self::MY => $myIds,
            self::USERS => $usersIds,
        };
    }

}
