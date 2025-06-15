<?php

namespace App\Enum;

enum EnumMonoAccount
{

    case TEST;

    public function getID(): string
    {
        return match ($this) {
            self::TEST => 'zr67YVE1yiW6qTBd_0WWyUgoms7y0gg',
        };
    }
    public function getSendId(): string
    {
        return match ($this) {
            self::TEST => '4DpjRdJeh',
        };
    }

}
