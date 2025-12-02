<?php

namespace App\Services\Telegram\Commands;

use App\Models\TelegramLogger;
use App\Services\Telegram\Dto\TelegramMessageDto;
use App\Services\Telegram\TelegramCommandHandler;

interface InterfaceCommand
{

    public static function action(TelegramMessageDto $telegramMessageDto): void;

    public static function secondAction(TelegramMessageDto $telegramMessageDto, ?string $text): void;
}
