<?php

namespace App\Services\Telegram\Dto;

/**
 * Натискання inline-кнопки (`callback_query` у вебхуку Telegram).
 */
class TelegramCallbackQueryDto
{
    public function __construct(
        private readonly array $json,
    ) {}

    public function getId(): string
    {
        return (string) ($this->json['id'] ?? '');
    }

    /** Хто натиснув кнопку: задає Telegram, тому це єдиний надійний ідентифікатор. */
    public function getFromId(): ?int
    {
        $id = data_get($this->json, 'from.id');

        return $id === null ? null : (int) $id;
    }

    public function getData(): ?string
    {
        return $this->json['data'] ?? null;
    }

    public function getChatId(): ?int
    {
        $id = data_get($this->json, 'message.chat.id');

        return $id === null ? null : (int) $id;
    }

    public function getMessageId(): ?int
    {
        $id = data_get($this->json, 'message.message_id');

        return $id === null ? null : (int) $id;
    }
}
