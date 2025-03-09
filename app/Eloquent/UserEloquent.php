<?php

namespace App\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserEloquent
{

    public static function search(Builder $query, string $search): Builder
    {
        $query->where(function ($q) use ($search) {
            $q->where('phone', 'like', "%{$search}%") // Поиск по номеру телефона
            ->orWhere('name', 'like', "%{$search}%") // Поиск по имени
            ->orWhere('telegram_nickname', 'like', "%{$search}%") // Поиск по нику в ТГ
            ->orWhereHas('cars', function ($carQuery) use ($search) { // Поиск по авто
                $carQuery->where('license_plate', 'like', "%{$search}%")
                    ->orWhere('personalized_license_plate', 'like', "%{$search}%");
            });
        });

        return $query;
    }

}
