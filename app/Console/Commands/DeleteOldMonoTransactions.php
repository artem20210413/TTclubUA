<?php

namespace App\Console\Commands;

use App\Models\MonoTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class DeleteOldMonoTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:mono-prune {days?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Видаляє mono_transactions старше за вказану кількість днів';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->argument('days') ?? 90;
        $cutoff = Carbon::now()->subDays($days);

        $count = MonoTransaction::where('created_at', '<', $cutoff)->delete();

        $this->info("Removed $count records created earlier than $cutoff.");

        return Command::SUCCESS;
    }
}
