<?php

namespace App\Enum;

enum NotificationsPushType: string
{
    case FA_FA = 'fa_fa';
    case MESSAGE = 'message';
    case EVENT = 'event';
    case DONATE = 'donate';
}
