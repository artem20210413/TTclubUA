<?php

namespace App\Services\Telegram;

use App\Services\Telegram\Commands\EnumTelegramCommands;
use Illuminate\Support\Facades\Cache;

class TelegramCommandState
{
    const PREFIX = 'tg:last_command:';

    /**
     * Записать команду в кеш на N минут
     */
    public static function store(int $chatId, ?EnumTelegramCommands $command, int $minutes = 10): void
    {
        if ($command)
            Cache::put(self::PREFIX . $chatId, $command->command(), now()->addMinutes($minutes));
    }

    /**
     * Получить последнюю активную команду
     */
    public static function get(int $chatId): ?EnumTelegramCommands
    {
        $value = Cache::get(self::PREFIX . $chatId);

        return $value
            ? EnumTelegramCommands::fromCommand($value)
            : null;
    }

    /**
     * Удалить состояние
     */
    public static function clear(int $chatId): void
    {
        Cache::forget(self::PREFIX . $chatId);
    }
}
