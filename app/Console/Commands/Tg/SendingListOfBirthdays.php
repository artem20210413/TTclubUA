<?php

namespace App\Console\Commands\Tg;

use App\Eloquent\UserEloquent;
use App\Enum\EnumTelegramEvents;
use App\Models\User;
use App\Services\Command\ListOfBirthdays;
use App\Services\Telegram\TelegramBot;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class SendingListOfBirthdays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tg:sending-list-of-birthdays {nextDays?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $nextDays = (int)$this->argument('nextDays');
        Log::info('search ListOfBirthdays nextDays: ' . $nextDays);

        $birthdayPeople = UserEloquent::getBirthdayPeople($nextDays);
        $botT = new TelegramBot(EnumTelegramEvents::LIST_BIRTHDAYS);
        $botT->sendMessage($this->getFormatStringBirthdayPeople($birthdayPeople, $nextDays));

        Log::info('finish ListOfBirthdays Count:' . $birthdayPeople->count());
    }


    public function getFormatStringBirthdayPeople(Collection $birthdayPeople, int $nextDays): ?string
    {
        $isToday = $nextDays === 0;
        $count = $birthdayPeople->count();
        if ($count === 0 && $isToday) return null;

        $text = $isToday
            ? "Сьогодні день народження у $count іменинників: \n"
            : "Наступного тижня маємо $count іменинників: \n";

        /** @var User $user */
        foreach ($birthdayPeople as $user) {
            $text .= "{$user->getShortInfo()}\n";
        }

        return $text;
    }
}
