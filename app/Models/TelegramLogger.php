<?php

namespace App\Models;

use App\Eloquent\TelegramLoggerEloquent;
use App\Enum\EnumTelegramLoggerDirection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramLogger extends Model
{
    public static function sendMessage(array $params)
    {
        // 1. отправляем сообщение
        $response = Telegram::sendMessage($params);

        TelegramLoggerEloquent::createOut($params);

        return $response;
    }

    public static function deleteMessage(array $params)
    {
        try {
            Telegram::deleteMessage($params);
            TelegramLoggerEloquent::createOutDelete($params);

        } catch (\Exception $e) {
            Log::error('Telegram deleteMessage error: ' . $e->getMessage());
        }
    }
}
