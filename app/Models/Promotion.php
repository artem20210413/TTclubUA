<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Promotion
 *
 * @property int $id
 * @property int $partner_id
 * @property string $promo_title
 * @property string|null $promo_description
 * @property string|null $discount_value
 * @property string|null $promo_code
 * @property boolean $is_exclusive
 * @property boolean $is_active
 * @property int $priority
 * @property string|null $start_date
 * @property string|null $end_date
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read \App\Models\Partner $partner
 */
class Promotion extends Model implements HasMedia, AuditableContract
{
    use InteractsWithMedia;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'partner_id',
        'promo_title',
        'promo_description',
        'discount_value',
        'promo_code',
        'is_exclusive',
        'is_active',
        'priority',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_exclusive' => 'boolean',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
