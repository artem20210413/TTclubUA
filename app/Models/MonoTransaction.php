<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string|null $hash Уникальный хеш для сверки
 * @property string|null $jar_id Идентификатор банки Monobank (если используется)
 * @property string|null source_ip
 * @property string|null $user_id
 * @property int|null $amount Сумма транзакции (в копейках)
 * @property int|null $currency_code Код валюты по ISO 4217
 * @property string|null $description Назначение платежа
 * @property string|null $comment Комментарий от пользователя
 * @property string $status Статус транзакции: pending, confirmed, rejected
 * @property array|null $payload Исходный JSON-ответ от Monobank
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class MonoTransaction extends Model
{
    protected $fillable = [
        'hash',
        'jar_id',
        'amount',
        'currency_code',
        'description',
        'comment',
        'status',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'amount' => 'integer',
        'currency_code' => 'integer',
    ];



    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createHash(User $user, string $jarId): void
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $this->hash =  hash('sha256', "{$jarId}|{$user->id}|{$timestamp}");
    }
}
