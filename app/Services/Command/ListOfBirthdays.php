<?php

namespace App\Services\Command;

use App\Eloquent\UserEloquent;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListOfBirthdays
{
    private Collection $birthdayPeople;

    public function __construct(readonly int $nextDays)
    {
        $this->birthdayPeople = UserEloquent::getBirthdayPeople($this->nextDays);
    }

    public function getBirthdayPeople(): Collection
    {
        return $this->birthdayPeople;
    }


    public function getFormatStringBirthdayPeople(): string
    {
        $count = $this->birthdayPeople->count();

        $text = "Наступного тижня маємо $count іменинників: \n";

        /** @var User $user */
        foreach ($this->birthdayPeople as $user) {
            $text .= "{$user->getShortInfo()}\n";
        }

        return $text;
    }


    public function getFormatStringBirthdayPeopleToday(): ?string
    {
        $count = $this->birthdayPeople->count();
        if ($count === 0) return null;
        $text = "Сьогодні день народження у $count іменинників: \n";

        /** @var User $user */
        foreach ($this->birthdayPeople as $user) {
            $text .= "{$user->getShortInfo()}\n"; // Исправлено добавление текста
        }

        return $text;
    }
}
