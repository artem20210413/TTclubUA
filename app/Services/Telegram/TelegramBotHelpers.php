<?php

namespace App\Services\Telegram;

use App\Models\User;
use Illuminate\Support\Facades\Lang;

class TelegramBotHelpers
{
    /**
     * Resolves a Telegram message template by translation key path and substitutes {token} placeholders.
     * Falls back to $default when the translation key is not found, matching the pre-refactor
     * config('telegram.messages.<key>', $default) behavior.
     */
    public static function renderTemplate(string $key, array $replacements = [], string $default = '---'): string
    {
        $template = Lang::has('telegram.'.$key) ? __('telegram.'.$key) : $default;

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

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
        if ($user?->telegram_id) {
            return self::MentionPerson($user);
        }

        if ($user?->telegram_nickname) {
            return self::LinkToPerson($user);
        }

        return $user?->name;
    }
}
