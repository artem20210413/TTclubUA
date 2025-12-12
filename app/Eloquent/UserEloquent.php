<?php

namespace App\Eloquent;

use App\Http\Controllers\Api\ApiException;
use App\Models\User;
use App\Services\Telegram\Dto\TelegramMessageDto;
use App\Services\Telegram\Dto\TelegramUserDto;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class UserEloquent
{

    public static function search(Builder $query, string $search): Builder
    {
        if (!$search) return $query;

        $query->where(function ($q) use ($search) {
            $searchLicense = formatNormalizePlateNumber($search);
            $q->where('phone', 'like', "%{$search}%") // Поиск по номеру телефона
            ->orWhere('name', 'like', "%{$search}%") // Поиск по имени
            ->orWhere('telegram_nickname', 'like', "%{$search}%") // Поиск по нику в ТГ
            ->orWhereHas('cars', function ($carQuery) use ($search) { // Поиск по авто
                $carQuery = CarEloquent::search($carQuery, $search);
            });
        });

        return $query;
    }

    public static function getBirthdayPeople(int $nextDays = 8, ?bool $isActive = null): Collection
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
            ->where('active', true)
            ->where('is_tt', true);
        if ($isActive !== null) {
            $users = $users->where('active', $isActive);
        }

        $users = $users->get();

        // Тут сортируем уже коллекцию по месяцу и дню рождения
        return $users->sortBy(function ($user) {
            return Carbon::parse($user->birth_date)->format('m-d');
        })->values();
//        $users->get()->sortBy(function ($user) {
//            return Carbon::parse($user->birth_date)->format('m-d');// Получаем месяц и день из даты рождения, игнорируя год
//        });
//
//        return $users;
    }

    public static function getNewMembersLastNDays(int $days): Collection
    {
        $fromDate = Carbon::today()->subDays($days);

        return User::query()
            ->where('created_at', '>=', $fromDate)->where('active', 1)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public static function countUsersWithCars(): int
    {
        return User::whereHas('cars')->count();
    }

    /**
     * @param int $UserTgId
     * @param array|null $contact
     * @return User|null
     * @throws ApiException
     */
    public static function updateByTg(TelegramMessageDto $messageDto): ?User
    {
        $user = User::query()->where('telegram_id', $messageDto->getFrom()->getId())->first();
        if ($user) return $user;
        if (!$messageDto->getContact()) return null;

        $phone = str_replace('+', '', $messageDto->getContact()['phone_number']);
        $user = User::query()->where('phone', $phone)->first();

        if (!$user) throw new ApiException("❗ Ми не знайшли ваш акаунт. Переконайтесь, що ви зареєстровані. Номер '$phone'");

        $user->telegram_id = $messageDto->getContact()['user_id'];
        if ($messageDto->getFrom()->getFirstName() !== null)
            $user->telegram_nickname = $messageDto->getFrom()->getUsername();
        $user->phone_verified_at = Carbon::now();
        $user->save();

        return $user;
    }


}
