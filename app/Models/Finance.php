<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Finance
 *
 * @property int id
 * @property int user_id
 * @property float amount
 * @property string|null description
 *
 *
 * @property \App\Models\User $user
 */
class Finance extends Model
{

    protected $fillable = [
        'id',
        'user_id',
        'amount',
        'description',
        'created_at',
        'updated_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
