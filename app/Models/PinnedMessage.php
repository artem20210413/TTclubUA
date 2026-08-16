<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $chat_id
 * @property string $message_id
 * @property \Illuminate\Support\Carbon|null $unpin_at
 * @property bool $delete_after_unpin
 */
class PinnedMessage extends Model
{
    protected $fillable = [
        'chat_id',
        'message_id',
        'unpin_at',
        'delete_after_unpin',
    ];

    protected $casts = [
        'unpin_at' => 'datetime',
        'delete_after_unpin' => 'boolean',
    ];
}
