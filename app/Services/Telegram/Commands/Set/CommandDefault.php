<?php

namespace App\Services\Telegram\Commands\Set;

use App\Models\TelegramLogger;
use App\Services\Telegram\Commands\InterfaceCommand;
use App\Services\Telegram\Dto\TelegramMessageDto;

class CommandDefault implements InterfaceCommand
{

    public static function action(TelegramMessageDto $telegramMessageDto): void
    {
        TelegramLogger::sendMessage([
            'chat_id' => $telegramMessageDto->getChat()->getId(),
            'text' => "🤖 Невідома команда. Введіть /help, щоб переглянути список доступних команд.",
        ]);
    }

    public static function secondAction(TelegramMessageDto $telegramMessageDto, ?string $text): void
    {
        // TODO: Implement secondAction() method.
    }
}
