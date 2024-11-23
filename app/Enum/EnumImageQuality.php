<?php

namespace App\Enum;

enum EnumImageQuality: string
{
    case LOW = 'low';
    case HD = 'hd';
    case FULL_HD = 'fullHD';

    public function getPx(): int
    {
        return match ($this) {
            self::LOW => 450,
            self::HD => 1366,
            self::FULL_HD => 1920,
        };
    }

    /**
     * Получить разрешение для качества
     */
    public function getResolution(): string
    {
        return match ($this) {
            self::LOW => '450x450',
            self::HD => '1366x768',
            self::FULL_HD => '1920x1080',
        };
    }
}
