<?php

namespace App\Services\Telegram\Commands;

use App\Enum\EnumTelegramLoggerDirection;
use App\Models\TelegramMessage;
use App\Services\Telegram\Commands\Set\CommandDefault;
use App\Services\Telegram\Commands\Set\CommandHelp;
use App\Services\Telegram\Commands\Set\CommandSetpassword;
use App\Services\Telegram\Commands\Set\CommandStart;
use App\Services\Telegram\Dto\TelegramMessageDto;
use App\Services\Telegram\TelegramCommandState;
use Illuminate\Support\Facades\Cache;

class TelegramCommands
{
    public function __construct(readonly TelegramMessageDto $dto)
    {
    }

    public function action(string $text)
    {
        $command = EnumTelegramCommands::fromCommand($text);

        match ($command) {
            EnumTelegramCommands::START => CommandStart::action($this->dto),
            EnumTelegramCommands::HELP => CommandHelp::action($this->dto),
            EnumTelegramCommands::SETPASSWORD => CommandSetpassword::action($this->dto),
            default => $this->secondAction($text)
        };

        TelegramCommandState::store(
            $this->dto->getChat()->getId(),
            $command,
            minutes: 10
        );

    }

    private function secondAction(string $text): void
    {
        $lastText = TelegramMessage::getLast($this->dto->getChat()->getId(), EnumTelegramLoggerDirection::IN, 1)?->text ?? null;

        $command = TelegramCommandState::get($this->dto->getChat()->getId())
            ? EnumTelegramCommands::fromCommand($lastText)
            : null;
        TelegramCommandState::clear($this->dto->getChat()->getId());

        match ($command) {
            EnumTelegramCommands::SETPASSWORD => CommandSetpassword::secondAction($this->dto, $text),
            default => CommandDefault::action($this->dto),
        };

    }

}
