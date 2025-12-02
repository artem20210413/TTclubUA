<?php

namespace App\Services\Telegram\Commands\Set;

use App\Models\TelegramLogger;
use App\Services\Telegram\Commands\InterfaceCommand;
use App\Services\Telegram\Dto\TelegramMessageDto;

class CommandGetPhone implements InterfaceCommand
{

    public static function action(TelegramMessageDto $telegramMessageDto): void
    {
        TelegramLogger::sendMessage([
            'chat_id' => $telegramMessageDto->getChat()->getId(),
            'text' => "👋 Привіт!\n\nДля продовження спілкування з ботом необхідно ідентифікувати себе.
            \n\nНатисни кнопку *«📞 Надіслати номер»* нижче, щоб поділитися своїм номером телефону. Це потрібно лише один раз і дозволить нам впевнено знати, хто ти 😊",
            'reply_markup' => json_encode([
                'keyboard' => [
                    [['text' => '📞 Надіслати номер', 'request_contact' => true]],
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true, // клавиатура исчезнет после нажатия
            ]),
        ]);
    }

    public static function secondAction(TelegramMessageDto $telegramMessageDto, ?string $text): void
    {
        // TODO: Implement secondAction() method.
    }
}
