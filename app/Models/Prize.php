<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Jetstream\HasProfilePhoto;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Prize
 *
 * @property int $id
 * @property int $draw_id
 * @property string $title Назва призу
 * @property int $quantity Кількість
 * @property int $sort_order Порядок розігрування
 * @property int|null $winner_participant_id ID переможця
 * @property-read Draw $draw
 * @property-read Participant|null $winner
 */
class Prize extends Model implements HasMedia
{
    use HasProfilePhoto;
    use InteractsWithMedia;

    protected $fillable = [
        'draw_id',
        'title',
        'quantity',
        'sort_order',
        'winner_participant_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'sort_order' => 'integer',
    ];

    public function draw()
    {
        return $this->belongsTo(Draw::class);
    }

    public function winner()
    {
        return $this->belongsTo(Participant::class, 'winner_participant_id');
    }
}
