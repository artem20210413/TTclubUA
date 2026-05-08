<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeployCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the code, dependencies, and caches the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('⏳ Starting deployment...');
        $php = PHP_BINARY;
        $commands = [
            'git pull origin main', // Change "main" to your branch if needed
            "$php /usr/local/bin/composer install",
            "$php artisan migrate",
            "$php artisan optimize",
//            'php artisan queue:restart',
//            'npm install && npm run build',
        ];

        foreach ($commands as $command) {
            $this->info("🚀 Running: $command");
            $output = shell_exec("$command 2>&1");

            if ($output) {
                $this->line($output);
            }
        }

        $this->info('✅ Deployment completed!');
        return 0;
    }
}
