<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramMessage extends Model
{
    protected $fillable = [
        'message_id', 'chat_id', 'direction', 'text', 'raw',
    ];

    protected $casts = [
        'raw' => 'array',
    ];
}
