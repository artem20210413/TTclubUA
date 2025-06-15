<?php

namespace App\Enum;

enum EnumMonoStatus
{

    case REJECTED;
    case CONFIRMED;
    case PENDING;

    public function getAlias(): string
    {
        return match ($this) {
            self::REJECTED => 'rejected',
            self::CONFIRMED => 'confirmed',
            self::PENDING => 'pending',
        };
    }

}
