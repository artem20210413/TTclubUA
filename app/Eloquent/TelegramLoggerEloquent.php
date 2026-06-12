<?php

namespace App\Eloquent;

use App\Enum\EnumTelegramLoggerDirection;
use App\Models\TelegramMessage;
use App\Services\Telegram\Dto\TelegramMessageDto;
use App\Services\Telegram\Dto\TelegramWebhookDto;
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

    public static function createIn(TelegramWebhookDto $telegramWebhookDto)
    {
        try {
            $t = new TelegramMessage();
            $t->chat_id = $telegramWebhookDto->getSmartChat()?->getId();
            $t->direction = EnumTelegramLoggerDirection::IN->value;
            $t->text = $telegramWebhookDto?->getMessage()?->getText();
            $t->raw = $telegramWebhookDto->json;
            $t->save();

            return $t;
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
        }

        return new TelegramMessage();
    }

    public static function createOutDelete(array $raw)
    {
        try {
            $t = new TelegramMessage();
            $t->chat_id = $raw['chat_id'] ?? null;
            $t->direction = EnumTelegramLoggerDirection::DELETE->value;
            $t->text = null;
            $t->raw = $raw;
            $t->save();

            return $t;
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
        }

        return new TelegramMessage();
    }


}
