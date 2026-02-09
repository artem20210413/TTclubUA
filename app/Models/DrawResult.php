<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DrawResult
 *
 * @property int $id
 * @property int $prize_id
 * @property int $participant_id
 * @property string $status Статус: confirmed, cancelled
 * @property \Carbon\Carbon $rolled_at Час проведення розіграшу
 * @property-read Prize $prize
 * @property-read Participant $participant
 */
class DrawResult extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'prize_id',
        'participant_id',
        'status',
        'rolled_at',
    ];

    protected $casts = [
        'rolled_at' => 'datetime',
    ];

    public function prize()
    {
        return $this->belongsTo(Prize::class);
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
}
