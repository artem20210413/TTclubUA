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
 *
 *
 * @property \App\Models\User $user
 */
class Color extends Model
{

    use HasFactory;
    public $timestamps = false;


}
