<?php

namespace App\Services\Telegram\Dto\ChatMember;
enum EnumTelegramChatMemberStatus: string
{
    // Користувач залишив чат самостійно (як у твоєму прикладі)
    case LEFT = 'left';

    // Звичайний активний учасник чату (вступив або повернувся)
    case MEMBER = 'member';

    // Творець (власник) групи або каналу
    case CREATOR = 'creator';

    // Адміністратор чату (має додаткові права)
    case ADMINISTRATOR = 'administrator';

    // Користувача забанили (вигнали та заблокували доступ)
    case KICKED = 'kicked';

    // Користувач обмежений у правах (наприклад, мут: заборонено писати)
    case RESTRICTED = 'restricted';
}
