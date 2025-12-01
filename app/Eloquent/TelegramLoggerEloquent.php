<?php

namespace App\Eloquent;

use App\Enum\EnumTelegramLoggerDirection;
use App\Models\TelegramMessage;
use Illuminate\Support\Facades\Log;

class TelegramLoggerEloquent
{

    public static function create(array $raw, EnumTelegramLoggerDirection $direction): TelegramMessage
    {
        try {
            $t = new TelegramMessage();
            $t->chat_id = $raw['chat_id'] ?? null;
            $t->direction = $direction->value;
            $t->text = $raw['text'] ?? null;
            $t->raw = $raw;
            $t->save();

            return $t;
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
        }

        return new TelegramMessage();
    }
}
