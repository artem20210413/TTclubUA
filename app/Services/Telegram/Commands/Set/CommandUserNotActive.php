<?php

namespace App\Services\Telegram\Commands\Set;

use App\Models\TelegramLogger;
use App\Services\Telegram\Commands\InterfaceCommand;
use App\Services\Telegram\Dto\TelegramMessageDto;

class CommandUserNotActive implements InterfaceCommand
{

    public static function action(TelegramMessageDto $telegramMessageDto): void
    {
        TelegramLogger::sendMessage([
            'chat_id' => $telegramMessageDto->getChat()->getId(),
            'text' => "⚠️ Ваш обліковий запис був видален.\n\nЗверніться до адміністратора або служби підтримки, щоб активувати доступ.",
        ]);
    }

    public static function secondAction(TelegramMessageDto $telegramMessageDto, ?string $text): void
    {
        // TODO: Implement secondAction() method.
    }
}
