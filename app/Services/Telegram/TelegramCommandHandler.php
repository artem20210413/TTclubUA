<?php

namespace App\Services\Telegram;

use App\Eloquent\TelegramLoggerEloquent;
use App\Models\TelegramLogger;
use App\Models\TelegramMessage;
use App\Services\Telegram\Commands\Set\CommandContactSuccessfully;
use App\Services\Telegram\Commands\Set\CommandGetPhone;
use App\Services\Telegram\Commands\Set\CommandHelp;
use App\Services\Telegram\Commands\Set\CommandStart;
use App\Services\Telegram\Commands\Set\CommandUserNotActive;
use App\Services\Telegram\Commands\TelegramCommands;
use App\Services\Telegram\Dto\TelegramMessageDto;
use App\Services\Telegram\Dto\TelegramWebhookDto;

class TelegramCommandHandler
{

    protected TelegramMessage $telegramMessage;
    protected ?TelegramMessageDto $telegramMessageDto = null;

    public function __construct(readonly TelegramWebhookDto $telegramWebhookDto)
    {
        $this->telegramMessageDto = $telegramMessageDto = $telegramWebhookDto->getMessage();
        $this->telegramMessage = TelegramLoggerEloquent::createIn($telegramWebhookDto);
        $text = $telegramMessageDto->getText() ?? '';

        if ($this->checkUser())
            $this->handleCommand($text);

    }

    private function checkUser(): bool
    {
        if (!$this->telegramMessageDto->getUser()) {
            CommandGetPhone::action($this->telegramMessageDto);
            return false;
        }

        if (!$this->telegramMessageDto->getUser()->active) {
            CommandUserNotActive::action($this->telegramMessageDto);
            return false;
        }

        if ($this->telegramMessageDto->getContact() !== null) {
            CommandContactSuccessfully::action($this->telegramMessageDto);
            return false;
        }

        return true;
    }

    private function handleCommand(string $text): void
    {
        $t = new TelegramCommands($this->telegramMessageDto);
        $t->action($text);
    }


}
