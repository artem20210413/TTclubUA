<?php

namespace App\Services\Telegram\Commands\Set;

use App\Notifications\CommandReplyNotification;
use App\Services\Telegram\Commands\EnumTelegramCommands;
use App\Services\Telegram\Commands\InterfaceCommand;
use App\Services\Telegram\Dto\TelegramMessageDto;
use App\Services\Telegram\TelegramBotHelpers;
use Illuminate\Support\Facades\Notification;

class CommandHelp implements InterfaceCommand
{
    public static function action(TelegramMessageDto $telegramMessageDto): void
    {
        $suffix = TelegramBotHelpers::renderTemplate('commands.help_suffix');
        $text = EnumTelegramCommands::helpText().$suffix;

        Notification::route('telegram', $telegramMessageDto->getChat()->getId())->notify(
            CommandReplyNotification::literal($text, parseMode: 'Markdown')
        );
    }

    public static function secondAction(TelegramMessageDto $telegramMessageDto, ?string $text): void
    {
        // TODO: Implement secondAction() method.
    }
}
