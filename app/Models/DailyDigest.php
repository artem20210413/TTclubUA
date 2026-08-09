<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property \Illuminate\Support\Carbon $digest_date
 * @property string $status
 * @property string|null $message
 * @property int $source_message_count
 * @property int $birthday_user_count
 * @property \Illuminate\Support\Carbon|null $delivered_at
 */
class DailyDigest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_FAILED = 'failed';

    public const STATUS_GREETINGS_ONLY = 'greetings_only';

    protected $fillable = [
        'digest_date',
        'status',
        'message',
        'prompt',
        'source_message_count',
        'total_messages',
        'top_active',
        'birthday_user_count',
        'delivered_at',
    ];

    protected $casts = [
        'digest_date' => 'date',
        'delivered_at' => 'datetime',
        'source_message_count' => 'integer',
        'total_messages' => 'integer',
        'top_active' => 'array',
        'birthday_user_count' => 'integer',
    ];

    public function isFinalized(): bool
    {
        return in_array($this->status, [self::STATUS_DELIVERED, self::STATUS_GREETINGS_ONLY], true);
    }
}
