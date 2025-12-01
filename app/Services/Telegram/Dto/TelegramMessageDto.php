<?php

namespace App\Services\Telegram\Dto;

use App\Eloquent\UserEloquent;
use App\Models\TelegramMessage;
use App\Models\User;

class TelegramMessageDto
{
    private ?TelegramUserDto $from = null;
    private ?TelegramChatDto $chat = null;
    private ?User $user;

    public function __construct(
        readonly array $json,
    )
    {
        if (!empty($json['from'])) {
            $this->from = new TelegramUserDto($json['from']);
        }

        if (!empty($json['chat'])) {
            $this->chat = new TelegramChatDto($json['chat']);
        }

        $this->user = UserEloquent::updateByTg($this);

    }

    public function getContact(): ?array
    {
        return $this->json['contact'] ?? null;
    }

    public function getMessageId(): ?int
    {
        return $this->json['message_id'] ?? null;
    }

    public function getFrom(): ?TelegramUserDto
    {
        return $this->from;
    }

    public function getChat(): ?TelegramChatDto
    {
        return $this->chat;
    }

    public function getDate(): ?int
    {
        return $this->json['date'] ?? null;
    }

    public function getText(): ?string
    {
        return $this->json['text'] ?? null;
    }

    public function getRaw(): array
    {
        return $this->json;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
