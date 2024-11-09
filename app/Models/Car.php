<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Car
 *
 * @property int $id
 * @property int|null $user_id
 * @property int $gene_id
 * @property int $model_id
 * @property string|null $name
 * @property string $license_plate
 * @property string|null $personalized_license_plate
 * @property string|null $photo_path
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property \App\Models\User $user
 * @property \App\Models\CarGene $gene
 * @property \App\Models\CarModel $model
 */
class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gene_id',
        'model_id',
        'name',
        'license_plate',
        'personalized_license_plate',
        'photo_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function gene()
    {
        return $this->belongsTo(CarGene::class, 'gene_id');
    }

    public function model()
    {
        return $this->belongsTo(CarModel::class, 'model_id');
    }
}
