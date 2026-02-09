<?php

namespace App\Models;

use App\Enum\DrawStatus;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Draw
 *
 * @property int $id
 * @property string $title Назва розіграшу
 * @property string|null $description Опис умов
 * @property DrawStatus $status Статус: planned, active, finished, cancelled
 * @property bool $allow_multiple_wins Чи може один учасник виграти кілька разів
 * @property bool $is_public Чи дозволена самостійна реєстрація
 * @property \Carbon\Carbon|null $registration_until Дата та час, до якого відкрита реєстрація
 * @property-read \Illuminate\Database\Eloquent\Collection|Participant[] $participants
 * @property-read \Illuminate\Database\Eloquent\Collection|Prize[] $prizes
 * @property-read \Illuminate\Database\Eloquent\Collection|DrawResult[] $results
 */
class Draw extends Model
{

    protected $fillable = [
        'title',
        'description',
        'status',
        'allow_multiple_wins',
        'is_public',
        'registration_until',
    ];

    protected $casts = [
        'status' => DrawStatus::class,
        'allow_multiple_wins' => 'boolean',
        'is_public' => 'boolean',
        'registration_until' => 'datetime',
    ];

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function prizes()
    {
        return $this->hasMany(Prize::class);
    }

    public function results()
    {
        return $this->hasMany(DrawResult::class);
    }
}
