<?php

namespace App\Console\Commands;

use App\Models\TelegramMessage;
use Illuminate\Console\Command;

class ClearTelegramMessagesCommand extends Command
{
    protected $signature = 'clear:telegram-messages {days=30}';

    protected $description = 'Delete collected telegram_messages older than N days (digest retention)';

    public function handle(): int
    {
        $this->info('⏳ Starting Clear...');

        $days = (int) $this->argument('days');

        $count = TelegramMessage::query()
            ->where('created_at', '<', now()->subDays($days))
            ->delete();

        $this->info("✅ Clear completed! Count: $count");

        return self::SUCCESS;
    }
}
