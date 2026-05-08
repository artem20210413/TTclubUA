<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * Class Finance
 *
 * @property int id
 * @property int user_id
 * @property string token
 * @property string platform
 * @property bool active
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 *
 * @property \App\Models\User $user
 */
class FcmToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'platform',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];


    /**
     * Зв'язок із користувачем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function updateToken(int $userId, string $token, ?string $platform = null): self
    {
        return self::updateOrCreate(
            ['token' => $token], // Пошук за унікальним токеном
            [
                'user_id'  => $userId,
                'platform' => $platform,
                'active'   => true,
            ]
        );
    }
}
