<?php

namespace App\Services\Telegram\Dto\ChatMember;

use App\Models\TelegramMessage;
use App\Services\Telegram\Dto\TelegramChatDto;
use App\Services\Telegram\Dto\TelegramUserDto;

class TelegramChatMemberDto
{


    private ?TelegramChatDto $chat = null;
    private ?TelegramUserDto $fromUser = null;
    private ?TelegramOldNewChatMemberDto $oldChatMember = null;
    private ?TelegramOldNewChatMemberDto $newChatMember = null;

    public function __construct(
        private readonly array $json,
    )
    {

        if (!empty($json['chat'])) {
            $this->chat = new TelegramChatDto($json['chat'] ?? []);
        }
        if (!empty($json['from'])) {
            $this->fromUser = new TelegramUserDto($json['from'] ?? []);
        }
        if (!empty($json['old_chat_member'])) {
            $this->oldChatMember = new TelegramOldNewChatMemberDto($json['old_chat_member'] ?? []);
        }
        if (!empty($json['new_chat_member'])) {
            $this->newChatMember = new TelegramOldNewChatMemberDto($json['new_chat_member'] ?? []);
        }

    }

    public function getChat(): ?TelegramChatDto
    {
        return $this->chat;
    }

    public function getFromUser(): ?TelegramUserDto
    {
        return $this->fromUser;
    }

    public function getOldChatMember(): ?TelegramOldNewChatMemberDto
    {
        return $this->oldChatMember;
    }

    public function getNewChatMember(): ?TelegramOldNewChatMemberDto
    {
        return $this->newChatMember;
    }

}
