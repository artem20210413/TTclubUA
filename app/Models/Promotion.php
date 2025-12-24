<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
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


    /**
     * Скоуп для получения только активных на данный момент акций.
     */
    public function scopeCurrentlyActive($query)
    {
        $now = now();
        $query->where(function ($q) use ($now) {
            $q->whereNull('start_date')->orWhereDate('start_date', '<=', $now);
        })->where(function ($q) use ($now) {
            $q->whereNull('end_date')->orWhereDate('end_date', '>=', $now);
        });

        return $query;
    }

    public function scopeIsActive($query, ?bool $active = null)
    {
        if ($active !== null)
            $query->where('is_active', $active);

        return $query;
    }

}
