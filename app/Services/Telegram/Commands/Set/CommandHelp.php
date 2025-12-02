<?php

namespace App\Services\Telegram\Commands\Set;

use App\Models\TelegramLogger;
use App\Services\Telegram\Commands\EnumTelegramCommands;
use App\Services\Telegram\Commands\InterfaceCommand;
use App\Services\Telegram\Dto\TelegramMessageDto;

class CommandHelp implements InterfaceCommand
{

    public static function action(TelegramMessageDto $telegramMessageDto): void
    {
        $text = EnumTelegramCommands::helpText() . "\n\nБільше можливостей з'явиться скоро. Якщо виникли питання — звертайтесь до підтримки.";
//        $text = "🆘 *Допомога — список команд:*
//                \n/start або /hi — привітання з ботом 🤖
//                \n/help — показати це повідомлення з переліком команд 📋
//                \n/changePassword {new-password} або /CP {new-password} — змінити пароль до вашого акаунта 🔐
//                \nБільше можливостей з'явиться скоро. Якщо виникли питання — звертайтесь до підтримки.";

        TelegramLogger::sendMessage([
            'chat_id' => $telegramMessageDto->getChat()->getId(),
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);
    }

    public static function secondAction(TelegramMessageDto $telegramMessageDto, ?string $text): void
    {
        // TODO: Implement secondAction() method.
    }
}
