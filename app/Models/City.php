<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class City
 *
 * @property int $id
 * @property string $name
 * @property string $country
 * @property float $latitude
 * @property float $longitude
 *
 *
 * @property \App\Models\User $user
 */
class City extends Model
{

    use HasFactory;
    public $timestamps = false;


    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
