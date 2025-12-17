<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Jetstream\HasProfilePhoto;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Publication
 *
 * @property int $id
 * @property string title
 * @property string description
 * @property float price
 * @property int priority
 * @property bool $active
 */
class Goods extends Model implements HasMedia, Auditable
{
    use HasProfilePhoto;
    use InteractsWithMedia;
    use AuditableTrait;

    protected $casts = [
        'active' => 'boolean',
    ];
}
