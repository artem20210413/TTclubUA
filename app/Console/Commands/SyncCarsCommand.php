<?php

namespace App\Console\Commands;

use App\Jobs\Autoria\SearchCarsJob;
use Illuminate\Console\Command;
class SyncCarsCommand extends Command
{
    // Название команды для терминала
    protected $signature = 'autoria_serch:sync';
    protected $description = 'Запуск процесу синхронізації авто з Auto.ria';

    public function handle()
    {
        $this->info('Відправляємо завдання на пошук авто в чергу.');

        // Запускаем ту самую джобу, которую мы написали раньше
        SearchCarsJob::dispatch();

        $this->info('Готово! Процес запущено у фоновому режимі.');
    }
}
