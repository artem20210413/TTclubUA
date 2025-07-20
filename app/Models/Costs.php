<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Finance
 *
 * @property int id
 * @property int owner_id
 * @property float amount
 * @property string|null description
 *
 *
 * @property \App\Models\User $user
 */
class Costs extends Model
{

    protected $fillable = [
        'id',
        'owner_id',
        'amount',
        'description',
        'created_at',
        'updated_at',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
