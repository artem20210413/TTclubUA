<?php

namespace App\Enum;

enum EnumTypeMedia: string
{
    case PROFILE_PICTURE = 'profile_picture';
    case PHOTO_COLLECTION = 'photo_collection';
    case PHOTO_MENTION = 'photo_mention';
    case PHOTO_EVENT = 'photo_event';
    case PHOTO_EVENT_TYPE = 'photo_event_type';

}
