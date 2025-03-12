<?php

namespace App\Services\Telegram;

use App\Enum\EnumTelegramChats;
use Illuminate\Support\Facades\Log;
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
            try {
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ]);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }


}
