<?php

namespace App\Services\Digest;

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
     * @return Collection<int, User> Greetable members whose birthday falls on $date.
     */
    public function membersForDate(CarbonInterface $date): Collection
    {
        // Match the birthday to the given date's month-day (ignoring year), same
        // active + is_tt scope as getBirthdayPeople, plus a required telegram_id.
        return User::query()
            ->whereRaw('DATE_FORMAT(birth_date, "%m-%d") = ?', [$date->format('m-d')])
            ->where('active', true)
            ->where('is_tt', true)
            ->whereNotNull('telegram_id')
            ->get();
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
