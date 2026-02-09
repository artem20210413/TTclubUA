<?php

namespace App\Enum;

enum EnumTypeMedia: string
{
    case PROFILE_PICTURE = 'profile_picture';
    case PHOTO_COLLECTION = 'photo_collection';
    case PHOTO_MENTION = 'photo_mention';
    case PHOTO_EVENT = 'photo_event';
    case PHOTO_GOODS = 'photo_goods';
    case PHOTO_DRAW = 'photo_draw';
    case PHOTO_EVENT_TYPE = 'photo_event_type';
    case PHOTO_PARTNER = 'photo_partner';
    case PHOTO_PARTNER_PROMOTION = 'photo_partner_promotion';

}
