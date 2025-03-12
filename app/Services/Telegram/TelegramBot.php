<?php

namespace App\Services\Telegram;

use App\Enum\EnumTelegramChats;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

class TelegramBot
{

    protected Api $telegram;

    /**
     * @throws TelegramSDKException
     */
    public function __construct(readonly EnumTelegramChats $enumTelegramChats)
    {
        $this->telegram = new Api(config('services.telegram.token'));
    }


    public function sendMessage(string $message)
    {
        foreach ($this->enumTelegramChats->getIds() as $chatId) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);
        }
    }


}
