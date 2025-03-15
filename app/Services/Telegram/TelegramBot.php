<?php

namespace App\Services\Telegram;

use App\Enum\EnumTelegramChats;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
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


    public function sendMessage(?string $message)
    {
        if (!$message) return;
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

    public function sendPhotoAndDescription(string $imgPath, ?string $description)
    {
        foreach ($this->enumTelegramChats->getIds() as $chatId) {
//            try {

            $this->telegram->sendPhoto([
                'chat_id' => $chatId,
                'photo' => fopen($imgPath, 'r'),
//                'photo' => fopen("https://tt.tishchenko.kiev.ua/storage/9/profile_image.webp", 'r'),
//                'photo' => fopen($file->getRealPath(), 'r'),
                'caption' => $description ?? '',
                'parse_mode' => 'HTML',
            ]);
//            } catch (\Exception $e) {
//                Log::error($e->getMessage());
//            }
        }
    }


}
