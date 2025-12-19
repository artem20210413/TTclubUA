<?php

namespace App\Console\Commands\Mentions;

use App\Eloquent\MentionEloquent;
use App\Models\Mention;
use Illuminate\Console\Command;

class ClearMentionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:mention {days?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear mentions older than N days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('⏳ Starting Clear...');

        $days = $this->argument('days') ?? 14;
        $mentions = Mention::where('created_at', '<', now()->subDays($days))->get();
        $count = count($mentions);
//        dd($mentions);
        foreach ($mentions as $mention) {
            MentionEloquent::fileDelete($mention);
            MentionEloquent::delete($mention);
        }

        $this->info("✅ Clear completed! Count: $count");
        return 0;
    }
}
