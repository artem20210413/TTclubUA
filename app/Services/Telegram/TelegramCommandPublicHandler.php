<?php

namespace App\Services\Telegram;

use App\Eloquent\TelegramLoggerEloquent;
use App\Enum\EnumTelegramEvents;
use App\Models\TelegramLogger;
use App\Models\TelegramMessage;
use App\Services\Telegram\Commands\Set\CommandContactSuccessfully;
use App\Services\Telegram\Commands\Set\CommandGetPhone;
use App\Services\Telegram\Commands\Set\CommandHelp;
use App\Services\Telegram\Commands\Set\CommandStart;
use App\Services\Telegram\Commands\Set\CommandUserNotActive;
use App\Services\Telegram\Commands\TelegramCommands;
use App\Services\Telegram\Dto\TelegramMessageDto;

class TelegramCommandPublicHandler
{

    protected TelegramMessage $telegramMessage;

    public function __construct(readonly TelegramMessageDto $telegramMessageDto)
    {

        $this->telegramMessage = TelegramLoggerEloquent::createIn($telegramMessageDto);

        if ($this->checkNewChatMembers())
            $this->handleNewChatMembers();

    }

    private function checkNewChatMembers(): bool
    {
        return !empty($this->telegramMessageDto->getNewChatMembers());
    }

    private function handleNewChatMembers()
    {

        $text = TelegramBotHelpers::generationTextNewChatMembers($this->telegramMessageDto->getNewChatMembers());
        $buttons = config('telegram.messages.new_member_welcome.links', []);

        $bot = new TelegramBot(EnumTelegramEvents::CUSTOM);
        $bot->setTelegramIds($this->telegramMessageDto->getChat()->getId());
        $res = $bot->sendMessage($text, $buttons); //TODO отложенное удаление этой записи
//        dd($res);
    }


}
