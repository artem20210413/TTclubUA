<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class Participant
 *
 * @property int $id
 * @property int $draw_id
 * @property int|null $user_id
 * @property string|null $name_manual Ім'я для анонімних учасників
 * @property string|null $contact_manual Контактні дані для анонімів
 * @property int $weight Вага учасника (шанси)
 * @property bool $is_winner Чи виграв цей учасник
 * @property-read Draw $draw
 * @property-read User|null $user
 */
class Participant extends Model
{

    protected $fillable = [
        'draw_id',
        'user_id',
        'name_manual',
        'contact_manual',
        'weight',
        'is_winner',
    ];

    protected $casts = [
        'weight' => 'integer',
        'is_winner' => 'boolean',
    ];

    public function draw()
    {
        return $this->belongsTo(Draw::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
