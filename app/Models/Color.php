<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class City
 *
 * @property int $id
 * @property string $name
 * @property string $hex
 * @property integer priority
 * @property bool active
 *
 *
 * @property \App\Models\User $user
 */
class Color extends Model
{protected $fillable = ['name', 'hex', 'active', 'priority'];

    use HasFactory;
    public $timestamps = false;


}
