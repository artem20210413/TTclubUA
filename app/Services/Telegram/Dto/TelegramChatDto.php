<?php

namespace App\Services\Telegram\Dto;

use App\Models\TelegramMessage;

class TelegramChatDto
{
    public function __construct(
        private readonly array $json,
    ) {}

    public function getId(): ?int
    {
        return $this->json['id'] ?? null;
    }

    public function getFirstName(): ?string
    {
        return $this->json['first_name'] ?? null;
    }

    public function getLastName(): ?string
    {
        return $this->json['last_name'] ?? null;
    }

    public function getUsername(): ?string
    {
        return $this->json['username'] ?? null;
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
