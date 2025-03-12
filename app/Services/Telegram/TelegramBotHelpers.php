<?php

namespace App\Services\Telegram;

use App\Models\User;

class TelegramBotHelpers
{
    public static function MentionPerson(User $user)
    {

//        $message = "<a href='tg://user?id={$userId}'>@$username</a>"; // Упоминание
    }

    public static function LinkToPerson(User $user)
    {
        return "<a href='https://t.me/{$user->telegram_nickname}'>$user->name</a> ";
    }
}
