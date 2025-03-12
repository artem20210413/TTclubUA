<?php

namespace App\Enum;

enum EnumTelegramChats
{

    case NOTIFICATION;

    public function getName(): string
    {
        return match ($this) {
            self::NOTIFICATION => 'Notification TT_club',
        };
    }

    /**
     * Получить разрешение для качества
     */
    public function getIds(): array
    {
        return match ($this) {
            self::NOTIFICATION => [-4635035669,],
        };
    }

}
