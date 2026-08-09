<?php

namespace App\Services\Digest;

use App\Eloquent\UserEloquent;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Builds a short Ukrainian birthday greeting for today's active main-chat members
 * who have a Telegram id (FR-004). Never exposes sensitive data such as phone or
 * birth year (FR-010) — only the display name (and public @nickname when present).
 */
class BirthdayGreetingProvider
{
    /**
     * @return Collection<int, User> Today's greetable members.
     */
    public function membersForDate(CarbonInterface $date): Collection
    {
        // getBirthdayPeople(0) already filters active + is_tt and matches today's month-day.
        return UserEloquent::getBirthdayPeople(0)
            ->filter(fn (User $user) => ! empty($user->telegram_id))
            ->values();
    }

    public function forDate(CarbonInterface $date): string
    {
        $members = $this->membersForDate($date);

        if ($members->isEmpty()) {
            return '';
        }

        $names = $members->map(function (User $user) {
            $name = trim((string) $user->name) ?: 'друже';

            return $user->telegram_nickname
                ? "{$name} (@{$user->telegram_nickname})"
                : $name;
        })->implode(', ');

        return "🎉 <b>Вітаємо з днем народження</b>: {$names}! Гарного дня та настрою! 🥳";
    }
}
