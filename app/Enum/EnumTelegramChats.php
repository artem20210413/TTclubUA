<?php

namespace App\Enum;

enum EnumTelegramChats
{

    case NOTIFICATION;
    case MENTION;
    case TEST;

    public function getName(): string
    {
        return match ($this) {
            self::NOTIFICATION => 'Notification TT_club',
            self::MENTION => 'fa-fa',
            self::TEST => 'fa-fa',
        };
    }

    /**
     * Получить разрешение для качества
     */
    public function getIds(): array
    {
        return match ($this) {
            self::NOTIFICATION => [-1002693142471],
            self::MENTION => [-4706815074],
            self::TEST => [-4706815074],
        };
    }

}
