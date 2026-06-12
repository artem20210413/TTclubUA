<?php

namespace App\Services\Telegram\Dto;

use App\Models\TelegramMessage;

class TelegramChatDto
{
    public function __construct(
        private readonly array $json,
    )
    {
    }

    public function getId(): ?int
    {
        return $this->json['id'] ?? null;
    }


    public function getTitle(): ?string
    {
        return $this->json['title'] ?? null;
    }

    public function getFirstName(): ?string
    {
        return $this->json['first_name'] ?? null;
    }

    public function getUserName(): ?string
    {
        return $this->json['username'] ?? null;
    }

    public function getSmartTitle(): ?string
    {
        return $this->getTitle() ?? $this->getUserName() ?? $this->getFirstName() ?? null;
    }

    public function getType(): ?string
    {
        return $this->json['type'] ?? null;
    }

    public function getRaw(): array
    {
        return $this->json;
    }
}
