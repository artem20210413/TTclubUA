<?php

namespace App\Services\Telegram\Commands;

enum EnumTelegramCommands
{
    case START;
    case HELP;
    case SETPASSWORD;

    /**
     * Повертає текст самої команди (без аргументів)
     */
    public function command(): string
    {
        return match ($this) {
            self::START        => '/start',
            self::HELP         => '/help',
            self::SETPASSWORD  => '/setpassword',
        };
    }

    /**
     * Повертає опис команди українською
     */
    public function description(): string
    {
        return match ($this) {
            self::START        => 'Почати роботу з ботом та отримати привітання.',
            self::HELP         => 'Показати список доступних команд.',
            self::SETPASSWORD  => 'Змінити пароль до вашого акаунта',
        };
    }

    /**
     * Повертає рядок для довідки
     */
    public function helpLine(): string
    {
        return sprintf(
            "%s — %s",
            $this->command(),
            $this->description()
        );
    }

    /**
     * Повний список команд
     */
    public static function helpList(): array
    {
        return array_map(
            fn(self $cmd) => $cmd->helpLine(),
            self::cases()
        );
    }

    /**
     * Повний текст довідки
     */
    public static function helpText(): string
    {
        $lines = array_map(
            fn(self $cmd) => $cmd->helpLine(),
            self::cases()
        );

        return "*Список доступних команд:*\n\n" . implode("\n", $lines);
    }

    public static function keyboard(int $columns = 2): array
    {
        $buttons = [];

        foreach (self::cases() as $case) {
            $buttons[] = ['text' => $case->command()];
        }

        // Розбиваємо кнопки на ряди
        $keyboard = array_chunk($buttons, $columns);

        return $keyboard;
    }


    /**
     * 🎯 ВАЖЛИВО!
     * Метод визначає, яка саме команда прийшла від Telegram.
     * Наприклад:
     * "/help"
     * "/setpassword newpass"
     * "/setpassword   qwerty"
     */
    public static function fromCommand(?string $input): ?self
    {
        if (!$input) {
            return null;
        }

        // Беремо тільки перше слово (команду), без аргументів
        $command = explode(' ', trim($input))[0];

        foreach (self::cases() as $case) {
            if ($case->command() === $command) {
                return $case;
            }
        }

        return null;
    }
}
