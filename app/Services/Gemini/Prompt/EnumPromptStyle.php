<?php

namespace App\Services\Gemini\Prompt;

enum EnumPromptStyle: string
{
    case FORMAL = 'formal';
    case FRIENDLY = 'friendly';
    case FUNNY = 'funny';
    case ROMANTIC = 'romantic';
    case CORPORATE = 'corporate';

    public function description(): string
    {
        return match ($this) {
            self::FORMAL    => 'офіційне, стримане та ввічливе привітання',
            self::FRIENDLY  => 'тепле, дружнє та приємне привітання',
            self::FUNNY     => 'жартівливе привітання з легким гумором',
            self::ROMANTIC  => 'романтичне, щире та ніжне привітання',
            self::CORPORATE => 'офіційне корпоративне привітання без зайвих емоцій',
        };
    }
}
