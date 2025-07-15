<?php

namespace App\Enum;

enum EnumMonoAccount
{

    case TEST;
    case LILIIA;

    public function getID(): string
    {
        return match ($this) {
            self::TEST => 'zr67YVE1yiW6qTBd_0WWyUgoms7y0gg',
                self::LILIIA => '5hBPRhnQWXGVxc2PwRR_D8N_w0ZtTMc',
        };
    }

    public function getSendId(): string
    {
        return match ($this) {
            self::TEST => '4DpjRdJeh',
            self::LILIIA => '27AGTbtTmv',
        };
    }

}
