<?php

namespace App\Services\Telegram\Commands\Set;

use App\Models\TelegramLogger;
use App\Services\Telegram\Commands\InterfaceCommand;
use App\Services\Telegram\Dto\TelegramMessageDto;

class CommandContactSuccessfully implements InterfaceCommand
{

    public static function action(TelegramMessageDto $telegramMessageDto): void
    {
        TelegramLogger::sendMessage([
            'chat_id' => $telegramMessageDto->getChat()->getId(),
            'text' => "✅ Дякуємо {$telegramMessageDto->getUser()->name}!\nМожемо продовжити спілкування 👌",
            'reply_markup' => json_encode([
                'remove_keyboard' => true, // убрать клавиатуру
            ]),
        ]);

        CommandStart::action($telegramMessageDto);
    }

    public static function secondAction(TelegramMessageDto $telegramMessageDto, ?string $text): void
    {
        // TODO: Implement secondAction() method.
    }
}
