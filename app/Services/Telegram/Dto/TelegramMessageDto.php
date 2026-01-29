<?php

namespace App\Services\Telegram\Dto;

use App\Eloquent\UserEloquent;
use App\Models\TelegramMessage;
use App\Models\User;

class TelegramMessageDto
{
    private ?TelegramUserDto $from = null;
    private array $newChatMembers = [];
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

        if (isset($json['new_chat_members']) && !empty($json['new_chat_members'])) {
            foreach ($json['new_chat_members'] as $member) {
                $this->newChatMembers[] = new TelegramUserDto($member);
            }
        }

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

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return array|TelegramUserDto[]
     */
    public function getNewChatMembers(): array
    {
        return $this->newChatMembers;
    }
}
