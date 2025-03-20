<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Jetstream\HasProfilePhoto;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class PublicationType
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $sort
 */
class PublicationType extends Model implements HasMedia
{
    use HasProfilePhoto;
    use InteractsWithMedia;
    public $timestamps = false;

    protected $fillable = ['id','name','description','sort'];
}
