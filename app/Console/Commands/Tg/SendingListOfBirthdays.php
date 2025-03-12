<?php

namespace App\Console\Commands\Tg;

use App\Enum\EnumTelegramChats;
use App\Services\Command\ListOfBirthdays;
use App\Services\Telegram\TelegramBot;
use Illuminate\Console\Command;
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
        $s = new ListOfBirthdays($nextDays);
        $botT = new TelegramBot(EnumTelegramChats::NOTIFICATION);
        $botT->sendMessage($s->getFormatStringBirthdayPeople());
        Log::info('finish ListOfBirthdays Count:' . $s->getBirthdayPeople()->count());
    }
}
