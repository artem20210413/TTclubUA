<?php

namespace App\Eloquent;

use App\Enum\EnumTelegramLoggerDirection;
use App\Models\TelegramMessage;
use App\Services\Telegram\Dto\TelegramMessageDto;
use Illuminate\Support\Facades\Log;

class TelegramLoggerEloquent
{

    public static function createOut(array $raw): TelegramMessage
    {
        try {
            $t = new TelegramMessage();
            $t->chat_id = $raw['chat_id'] ?? null;
            $t->direction = EnumTelegramLoggerDirection::OUT->value;
            $t->text = $raw['text'] ?? null;
            $t->raw = $raw;
            $t->save();

            return $t;
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
        }

        return new TelegramMessage();
    }

    public static function createIn(TelegramMessageDto $telegramMessageDto)
    {
        try {
            $t = new TelegramMessage();
            $t->chat_id = $telegramMessageDto->getChat()->getId();
            $t->direction = EnumTelegramLoggerDirection::IN->value;
            $t->text = $telegramMessageDto->getText();
            $t->raw = $telegramMessageDto->json;
            $t->save();

            return $t;
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
        }

        return new TelegramMessage();
    }

}
