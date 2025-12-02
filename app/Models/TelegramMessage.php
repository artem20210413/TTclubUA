<?php

namespace App\Models;

use App\Enum\EnumTelegramLoggerDirection;
use Illuminate\Database\Eloquent\Model;

class TelegramMessage extends Model
{
    protected $fillable = [
        'message_id', 'chat_id', 'direction', 'text', 'raw',
    ];

    protected $casts = [
        'raw' => 'array',
    ];

    public static function getLast(int $chatId, EnumTelegramLoggerDirection $direction, int $offset = 0): ?self
    {
        return self::query()
            ->where('chat_id', $chatId)
            ->where('direction', $direction)
            ->orderBy('id', 'desc')
            ->skip($offset)     // offset = 0 → самое последнее
            ->first();
    }

}
