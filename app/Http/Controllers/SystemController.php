<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SystemController extends Controller
{
    /**
     * Получение системной статистики по пользователям.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function systemUserStats(): JsonResponse
    {
        // --- Базовая статистика ---
        $totalUsers = User::query()->count();
        $newUsersToday = User::query()->whereDate('created_at', Carbon::today())->count();
        $newUsersLast30Days = User::query()->where('created_at', '>=', Carbon::now()->subDays(30))->count();
        $newUsersLast365Days = User::query()->where('created_at', '>=', Carbon::now()->subDays(365))->count();
        $newUsersThisMonth = User::query()->where('created_at', '>=', Carbon::now()->startOfMonth())->count();
        $newUsersThisYear = User::query()->where('created_at', '>=', Carbon::now()->startOfYear())->count();

        // --- Статистика по активности ---
        $activeSince = Carbon::now()->subHour();
        $activeUsersLastHourIds = DB::table('personal_access_tokens')
            ->where('last_used_at', '>=', $activeSince)
            ->distinct('tokenable_id')
            ->pluck('tokenable_id');

        // --- Статистика по токенам ---
        $usersWithMultipleTokens = DB::table('personal_access_tokens')
            ->select('tokenable_id', DB::raw('count(*) as token_count'))
            ->groupBy('tokenable_id')
            ->having('token_count', '>', 1)
            ->get();

        // --- Количество уникальных авторизованных пользователей ---
        $authorizedUniqueUsers = DB::table('personal_access_tokens')
            ->distinct('tokenable_id')
            ->count();

        // --- Распределение по статусу ---
        $activeUsersCount = User::query()->where('active', true)->count();
        $inactiveUsersCount = $totalUsers - $activeUsersCount;

        // --- Пользователи без машин ---
        $usersWithoutCars = User::query()->doesntHave('cars');
        $usersWithCars = User::query()->has('cars');

        return response()->json([
            'total_users' => $totalUsers,
            'authorized_unique_users' => $authorizedUniqueUsers,
            'active_users_last_hour' => [
                'count' => $activeUsersLastHourIds->count(),
                'user_ids' => $activeUsersLastHourIds,
            ],
            'new_users' => [
                'today' => $newUsersToday,
                'last_30_days' => $newUsersLast30Days,
                'last_365_days' => $newUsersLast365Days,
                'this_month' => $newUsersThisMonth,
                'this_year' => $newUsersThisYear,
            ],
            'user_status_distribution' => [
                'active' => $activeUsersCount,
                'inactive' => $inactiveUsersCount,
            ],
            'users_with_multiple_tokens' => [
                'count' => $usersWithMultipleTokens->count(),
                'user_ids' => $usersWithMultipleTokens->pluck('tokenable_id'),
            ],
            'users_without_cars' => [
                'count' => $usersWithoutCars->count(),
//                'user_ids' => $usersWithoutCars->pluck('id'),
            ],
            'users_with_cars' => [
                'count' => $usersWithCars->count(),
//                'user_ids' => $usersWithCars->pluck('id'),
            ],
        ]);

    }
}
