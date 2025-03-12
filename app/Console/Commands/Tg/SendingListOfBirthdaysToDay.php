<?php

namespace App\Console\Commands\Tg;

use App\Enum\EnumTelegramChats;
use App\Services\Command\ListOfBirthdays;
use App\Services\Telegram\TelegramBot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendingListOfBirthdaysToDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tg:sending-list-of-birthdays-today';

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
        Log::info('search SendingListOfBirthdaysToDay');
        $s = new ListOfBirthdays(0);
        $botT = new TelegramBot(EnumTelegramChats::NOTIFICATION);
        $botT->sendMessage($s->getFormatStringBirthdayPeopleToday());
        Log::info('finish SendingListOfBirthdaysToDay Count:' . $s->getBirthdayPeople()->count());
    }
}
