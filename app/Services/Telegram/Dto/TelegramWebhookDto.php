<?php

namespace App\Services\Telegram\Dto;

use App\Eloquent\UserEloquent;
use App\Models\TelegramMessage;
use App\Models\User;
use App\Services\Telegram\Dto\ChatMember\TelegramChatMemberDto;
use App\Services\Telegram\Dto\ChatMember\TelegramMyChatMemberDto;

class TelegramWebhookDto
{
    private ?TelegramMessageDto $message = null;
    private ?TelegramChatMemberDto $chatMember = null;
    private ?TelegramMyChatMemberDto $myChatMember = null;
    private ?TelegramChatDto $smartChat = null;

    public function __construct(
        readonly array $json,
    )
    {
        if (!empty($json['message'])) {
            $this->message = new TelegramMessageDto($json['message']);
        }
        if (!empty($json['chat_member'])) {
            $this->chatMember = new TelegramChatMemberDto($json['chat_member']);
        }
        if (!empty($json['my_chat_member'])) {
            $this->myChatMember = new TelegramMyChatMemberDto($json['my_chat_member']);
        }

        $this->smartChat = match (true) {
            isset($this->message) => $this->message->getChat(),
            isset($this->chatMember) => $this->chatMember->getChat(),
            isset($this->myChatMember) => $this->myChatMember->getChat(),
            default => null,
        };

    }

    public function getMessage(): ?TelegramMessageDto
    {
        return $this->message;
    }

    public function getChatMember(): ?TelegramChatMemberDto
    {
        return $this->chatMember;
    }

    public function getMyChatMember(): ?TelegramMyChatMemberDto
    {
        return $this->myChatMember;
    }

    public function getSmartChat(): ?TelegramChatDto
    {
        return $this->smartChat;
    }

}
