<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Jetstream\HasProfilePhoto;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Publication
 *
 * @property int id
 * @property int $user_id
 * @property int event_type_id
 * @property string title
 * @property string description
 * @property string place
 * @property \Carbon\Carbon|null event_date
 * @property string google_maps_url
 * @property bool $active
 * @property \Carbon\Carbon|null created_at
 * @property \Carbon\Carbon|null updated_at
 */
class Event extends Model implements HasMedia, AuditableContract
{
    use HasProfilePhoto;
    use InteractsWithMedia;
    use \OwenIt\Auditing\Auditable;


    protected $fillable = [
        'user_id',
        'event_type_id',
        'title',
        'description',
        'place',
        'event_date',
        'google_maps_url',
        'active',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($event) {
            /** @var Event $event */
            if (auth()->check() && empty($event->user_id)) {
                $event->user_id = auth()->id();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function eventType()
    {
        return $this->belongsTo(EventType::class);
    }
}
