<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Partner
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string|null $website_url
 * @property string|null $instagram_url
 * @property string|null $google_maps_url
 * @property int $priority
 * @property boolean $is_active
 * @property string|null $start_date
 * @property string|null $end_date
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Partner extends Model implements HasMedia, AuditableContract
{
    use InteractsWithMedia;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'title',
        'description',
        'website_url',
        'instagram_url',
        'google_maps_url',
        'priority',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    public function promotions_actual()
    {
        $today = Carbon::today();

        return $this->hasMany(Promotion::class)
            ->where('is_active', true)
            ->where(function ($query) use ($today) {
                $query->WhereDate('start_date', '<=', $today)
                      ->WhereDate('end_date', '>=', $today);
            });
    }
}
