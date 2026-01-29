<?php

namespace App\Enum;

enum UpdateStatusEnum: string
{
    case UP_TO_DATE = 'UP_TO_DATE';
    case UPDATE_AVAILABLE = 'UPDATE_AVAILABLE';
    case FORCE_UPDATE = 'FORCE_UPDATE';
}
