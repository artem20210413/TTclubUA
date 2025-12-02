<?php

namespace App\Services\Telegram\Commands\Set;

use App\Models\TelegramLogger;
use App\Services\Telegram\Commands\EnumTelegramCommands;
use App\Services\Telegram\Commands\InterfaceCommand;
use App\Services\Telegram\Dto\TelegramMessageDto;

class CommandStart implements InterfaceCommand
{

    public static function action(TelegramMessageDto $telegramMessageDto): void
    {
        TelegramLogger::sendMessage([
            'chat_id' => $telegramMessageDto->getChat()->getId(),
            'text' => "Привіт {$telegramMessageDto->getUser()->name}!\nЯ Telegram-бот Клубу TT.\nЩоб дізнатися що я вмію напиши команду '/help'",
            'reply_markup' => json_encode([
                'keyboard' => EnumTelegramCommands::keyboard(),
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]),
        ]);
    }

    public static function secondAction(TelegramMessageDto $telegramMessageDto, ?string $text): void
    {
        // TODO: Implement secondAction() method.
    }
}
