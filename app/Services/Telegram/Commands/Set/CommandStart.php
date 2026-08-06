<?php

namespace App\Services\Telegram\Commands\Set;

use App\Notifications\CommandReplyNotification;
use App\Services\Telegram\Commands\EnumTelegramCommands;
use App\Services\Telegram\Commands\InterfaceCommand;
use App\Services\Telegram\Dto\TelegramMessageDto;
use Illuminate\Support\Facades\Notification;

class CommandStart implements InterfaceCommand
{
    public static function action(TelegramMessageDto $telegramMessageDto): void
    {
        Notification::route('telegram', $telegramMessageDto->getChat()->getId())->notify(
            new CommandReplyNotification('commands.start', [
                '{name}' => $telegramMessageDto->getUser()->name,
            ], replyMarkup: [
                'keyboard' => EnumTelegramCommands::keyboard(),
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ])
        );
    }

    public static function secondAction(TelegramMessageDto $telegramMessageDto, ?string $text): void
    {
        // TODO: Implement secondAction() method.
    }
}
