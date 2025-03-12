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


    public function getFormatStringBirthdayPeople()
    {
        $count = $this->birthdayPeople->count();

        $text = "Наступного тижня маємо $count іменинників: \n";

        /** @var User $user */
        foreach ($this->birthdayPeople as $user) {
            $text .= "{$user->birth_date} {$user->name} - {$user->telegram_nickname}\n"; // Исправлено добавление текста
        }

        return $text;
    }
}
