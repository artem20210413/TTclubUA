<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class CarGene
 *
 * @property int $id
 * @property string $name
 * @property string|null $photo_path
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class CarGene extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'photo_path',
    ];

    public function cars():HasMany
    {
        return $this->hasMany(Car::class, 'gene_id');
    }
}
