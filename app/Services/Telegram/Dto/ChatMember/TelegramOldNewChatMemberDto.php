<?php

namespace App\Services\Telegram\Dto\ChatMember;

use App\Models\TelegramMessage;
use App\Services\Telegram\Dto\TelegramChatDto;
use App\Services\Telegram\Dto\TelegramUserDto;

class TelegramOldNewChatMemberDto
{


    private ?TelegramUserDto $user = null;

    public function __construct(
        private readonly array $json,
    )
    {
        if (!empty($json['user'])) {
            $this->user = new TelegramUserDto($json['user']);
        }

    }

    public function getUser(): ?TelegramUserDto
    {
        return $this->user;
    }

    public function getStatus(): ?EnumTelegramChatMemberStatus
    {
        return EnumTelegramChatMemberStatus::tryFrom($this->json['status'] ?? '');
    }

    public function getTag(): ?string
    {
        return $this->json['tag'] ?? null;
    }

}
