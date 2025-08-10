<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Jetstream\HasProfilePhoto;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Publication
 *
 * @property int id
 * @property string name
 * @property int sort
 */
class EventType extends Model implements HasMedia
{
    use HasProfilePhoto;
    use InteractsWithMedia;

    public $timestamps = false;
}
