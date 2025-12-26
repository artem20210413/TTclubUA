<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $platform
 * @property string $latest_version
 * @property string $min_version
 * @property string $store_url
 * @property string|null $release_notes
 * @property bool $is_active
 */
class AppConfig extends Model
{

    protected $fillable = [
        'platform',
        'latest_version',
        'min_version',
        'store_url',
        'release_notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
