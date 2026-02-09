<?php

namespace App\Enum;

enum DrawStatus: string
{
    case PLANNED = 'planned';
    case ACTIVE = 'active';
    case FINISHED = 'finished';
    case CANCELLED = 'cancelled';
}
