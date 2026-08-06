<?php

namespace App\Services\Telegram\Commands\Set;

use App\Notifications\CommandReplyNotification;
use App\Services\Telegram\Commands\InterfaceCommand;
use App\Services\Telegram\Dto\TelegramMessageDto;
use Illuminate\Support\Facades\Notification;

class CommandUserNotActive implements InterfaceCommand
{
    public static function action(TelegramMessageDto $telegramMessageDto): void
    {
        Notification::route('telegram', $telegramMessageDto->getChat()->getId())->notify(
            new CommandReplyNotification('commands.user_not_active')
        );
    }

    public static function secondAction(TelegramMessageDto $telegramMessageDto, ?string $text): void
    {
        // TODO: Implement secondAction() method.
    }
}
