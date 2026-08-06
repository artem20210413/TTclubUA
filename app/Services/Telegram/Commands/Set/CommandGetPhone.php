<?php

namespace App\Services\Telegram\Commands\Set;

use App\Notifications\CommandReplyNotification;
use App\Services\Telegram\Commands\InterfaceCommand;
use App\Services\Telegram\Dto\TelegramMessageDto;
use App\Services\Telegram\TelegramBotHelpers;
use Illuminate\Support\Facades\Notification;

class CommandGetPhone implements InterfaceCommand
{
    public static function action(TelegramMessageDto $telegramMessageDto): void
    {
        $buttonLabel = TelegramBotHelpers::renderTemplate('commands.get_phone_button', [], '📞 Надіслати номер');

        Notification::route('telegram', $telegramMessageDto->getChat()->getId())->notify(
            new CommandReplyNotification('commands.get_phone', [], replyMarkup: [
                'keyboard' => [
                    [['text' => $buttonLabel, 'request_contact' => true]],
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => true, // клавиатура исчезнет после нажатия
            ])
        );
    }

    public static function secondAction(TelegramMessageDto $telegramMessageDto, ?string $text): void
    {
        // TODO: Implement secondAction() method.
    }
}
