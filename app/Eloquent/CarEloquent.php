<?php

namespace App\Eloquent;

use App\Models\Car;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CarEloquent
{

    public static function search(Builder $query, string $search): Builder
    {
        $searchLicense = formatNormalizePlateNumber($search);
        $query->where('license_plate', 'like', "%{$searchLicense}%")
            ->orWhere('personalized_license_plate', 'like', "%{$searchLicense}%");

        return $query;
    }

    public static function countCarsWithUsers(): int
    {
        return Car::whereHas('user')->count();
    }
}
