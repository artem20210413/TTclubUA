<?php

namespace App\Models;

use App\Enum\NotificationsPushType;
use App\Services\Fcm\FcmService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'type',
        'data',
        'read_at',
    ];

    protected $casts = [
        // Автоматично перетворює JSON з бази у масив Dart-style
        'data' => 'array',
        'type' => NotificationsPushType::class, // Кастуємо в твій Enum
        'read_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::created(function ($notification) {
            FcmService::pushNotification($notification);
        });
    }

    /**
     * Користувач, якому належить сповіщення
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Помітити як прочитане
     */
    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }
}
