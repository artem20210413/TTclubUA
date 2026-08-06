<?php

namespace App\Services\Telegram\Commands;

use App\Services\Telegram\Dto\TelegramMessageDto;

interface InterfaceCommand
{
    public static function action(TelegramMessageDto $telegramMessageDto): void;

    public static function secondAction(TelegramMessageDto $telegramMessageDto, ?string $text): void;
}
