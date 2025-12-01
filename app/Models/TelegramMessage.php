<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramMessage extends Model
{
    protected $fillable = [
        'user_id', 'telegram_id', 'chat_id', 'direction', 'text', 'raw',
    ];

    protected $casts = [
        'raw' => 'array',
    ];
}
