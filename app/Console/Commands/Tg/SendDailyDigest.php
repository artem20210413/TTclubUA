<?php

namespace App\Console\Commands\Tg;

use App\Jobs\SendDailyDigestJob;
use Illuminate\Console\Command;

class SendDailyDigest extends Command
{
    protected $signature = 'tg:send-daily-digest {--date= : Target day YYYY-MM-DD (default: today)} {--sync : Run inline instead of dispatching to the queue}';

    protected $description = 'Generate and send the daily AI chat digest to the main chat';

    public function handle(): int
    {
        $date = $this->option('date') ?: null;

        if ($this->option('sync')) {
            SendDailyDigestJob::dispatchSync($date);
        } else {
            SendDailyDigestJob::dispatch($date);
        }

        $this->info('Daily digest job dispatched'.($date ? " for {$date}" : '').'.');

        return self::SUCCESS;
    }
}
