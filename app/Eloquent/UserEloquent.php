<?php

namespace App\Eloquent;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class UserEloquent
{

    public static function search(Builder $query, string $search): Builder
    {
        $query->where(function ($q) use ($search) {
            $searchLicense = formatNormalizePlateNumber($search);
            $q->where('phone', 'like', "%{$search}%") // Поиск по номеру телефона
            ->orWhere('name', 'like', "%{$search}%") // Поиск по имени
            ->orWhere('telegram_nickname', 'like', "%{$search}%") // Поиск по нику в ТГ
            ->orWhereHas('cars', function ($carQuery) use ($searchLicense) { // Поиск по авто
                $carQuery->where('license_plate', 'like', "%{$searchLicense}%")
                    ->orWhere('personalized_license_plate', 'like', "%{$searchLicense}%");
            });
        });

        return $query;
    }

    public static function getBirthdayPeople(int $nextDays = 8): Collection
    {
        $today = Carbon::today();  // Текущая дата
        $birthdayNext8Days = Carbon::today()->addDays($nextDays);  // Дата через 8 дней

// Форматируем дату для сравнения (только месяц и день)
        $todayFormatted = $today->format('m-d');
        $birthdayNext8DaysFormatted = $birthdayNext8Days->format('m-d');

// Получаем пользователей, чьи дни рождения находятся в промежутке от сегодня до 8 дней вперед
        $users = User::whereRaw('DATE_FORMAT(birth_date, "%m-%d") BETWEEN ? AND ?', [
            $todayFormatted,
            $birthdayNext8DaysFormatted,
        ])
            ->get()->sortBy(function ($user) {
                return Carbon::parse($user->birth_date)->format('m-d');// Получаем месяц и день из даты рождения, игнорируя год
            });

        return $users;
    }

}
