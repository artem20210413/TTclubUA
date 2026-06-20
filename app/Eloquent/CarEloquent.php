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

    public static function search(Builder $query, ?string $search): Builder
    {
        if (!$search) return $query;

        $searchLicense = formatNormalizePlateNumber($search);
        $query->where('license_plate', 'like', "%{$searchLicense}%")
            ->orWhere('personalized_license_plate', 'like', "%{$searchLicense}%");

        return $query;
    }

    public static function searchByUser(Builder $query, ?string $search): Builder
    {
        if (!$search) return $query;
        $words = array_filter(explode(' ', preg_replace('/\s+/', ' ', trim($search))));

        $query->orWhereHas('user', function ($userQuery) use ($search, $words) { // Поиск по авто
            $userQuery->where('phone', 'like', "%{$search}%") // Поиск по номеру телефона
            ->orWhere('name', 'like', "%{$search}%") // Поиск по имени
            ->orWhere('telegram_nickname', 'like', "%{$search}%") // Поиск по нику в ТГ
            ->orWhere('occupation_description', 'like', "%{$search}%") // Поиск по нику в ТГ
            ->orWhere('instagram_nickname', 'like', "%{$search}%"); // Поиск по нику в ТГ
            $userQuery->orWhere(function ($subQuery) use ($words) {
                foreach ($words as $word) {
                    $subQuery->orWhere('occupation_description', 'like', "%{$word}%");
                }
            });
        });

        return $query;
    }

    public static function countCarsWithUsers(): int
    {
        return Car::whereHas('user')->count();
    }

    public static function onlyActive(Builder $q)
    {
        return $q->where('active', true);
    }

    public static function onlyActiveUser(Builder $q)
    {
        return $q->whereHas('user', function ($q) {
            $q->where('active', true);
        });
    }
}
