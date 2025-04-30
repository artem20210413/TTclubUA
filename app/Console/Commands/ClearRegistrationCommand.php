<?php

namespace App\Console\Commands;

use App\Eloquent\MentionEloquent;
use App\Eloquent\RegistrationEloquent;
use App\Models\Mention;
use App\Models\Registration;
use Illuminate\Console\Command;

class ClearRegistrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:registration {days?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear registration older than N days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('⏳ Starting Clear...');

        $days = $this->argument('days') ?? 90;
        $registrations = Registration::where('approve', false)->where('active', false)->where('created_at', '<', now()->subDays($days))->get();
        $count = count($registrations);

        foreach ($registrations as $r) {
            RegistrationEloquent::delete($r);
        }

        $this->info("✅ Clear completed! Count: $count");
        return 0;
    }
}
