<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditPrune extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:prune {--days=90 : The number of days to retain audit records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune old records from the audits table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');

        if (!is_numeric($days) || $days <= 0) {
            $this->error('Please provide a valid number of days.');
            return 1;
        }

        $this->info("Pruning audit records older than {$days} days...");

        $cutOffDate = Carbon::now()->subDays($days);

        $deletedRows = DB::table('audits')->where('created_at', '<', $cutOffDate)->delete();

        $this->info("Done. Pruned {$deletedRows} records.");

        return 0;
    }
}
