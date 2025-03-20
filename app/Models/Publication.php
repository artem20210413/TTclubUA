<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Jetstream\HasProfilePhoto;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Publication
 *
 * @property int $id
 * @property int $user_id
 * @property int $publication_type_id
 * @property string $title
 * @property string $name
 * @property string $description
 * @property float $latitude
 * @property float $longitude
 * @property bool $active
 */
class Publication extends Model implements HasMedia
{

    use HasProfilePhoto;
    use InteractsWithMedia;
}
