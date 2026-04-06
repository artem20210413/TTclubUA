<?php

namespace App\Enum;

enum EnumUserRoles: string
{

    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case USER = 'user';
    case TESTER = 'tester';
    case TTOWNER = 'TTowner';
    case PRIVILEGED = 'privileged';

    // Метод для получения перевода
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Адміністратор',
            self::EDITOR => 'Редактор',
            self::USER => 'Користувач',
            self::TESTER => 'Тестувальник',
            self::TTOWNER => 'Власник Audi ТТ',
            self::PRIVILEGED => 'Привілейований користувач',
        };
    }

    /**
     * Повертає опис прав та особливостей кожної ролі.
     * * @return string
     */
    public function description(): string
    {
        return match ($this) {
            self::ADMIN => 'Повний доступ до всіх налаштувань системи та керування користувачами.',
            self::EDITOR => 'Нічого не робить',
            self::USER => 'Нічого не робить',
            self::TESTER => 'Доступ до функціоналу тестування.',
            self::TTOWNER => 'Є або був власником Audi TT та є членом клубу.',
            self::PRIVILEGED => 'Користувач, з якого не стягуються обов\'язкові платежі.',
        };
    }

    // Метод для збору всіх даних у масив
    public static function apiList(): array
    {
        return array_map(fn($case) => [
            'alias' => $case->value,
            'name' => $case->label(),
            'description' => $case->description(),
        ], self::cases());
    }

//    foreach (EnumUserRoles::cases() as $role) {
//    echo "- **{$role->value}**: {$role->label()}\n";
//    }
}
