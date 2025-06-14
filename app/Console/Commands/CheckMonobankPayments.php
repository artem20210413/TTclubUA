<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckMonobankPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-monobank-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check recent Monobank payments and match with payment hashes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = config('services.monobank.token');
        if (!$token) {
            $this->error('Monobank token not set.');
            return 1;
        }

        $idBank = "";
        $now = now()->timestamp;
        $from = now()->subHours(3)->timestamp; // берём за последние 3 часа (можно больше)
//        dd("https://api.monobank.ua/personal/statement/$idBank/{$from}/{$now}");
        $response = Http::withHeader('X-Token',$token)
            ->get("https://api.monobank.ua/personal/statement/$idBank/{$from}/{$now}");
//dd($response->body());
        if (!$response->ok()) {
            dd($response->body());
            $this->error('Ошибка при получении данных Monobank');
            return 1;
        }

        $transactions = $response->json();

        foreach ($transactions as $txn) {
            $description = $txn['comment'] ?? '';
            if (preg_match('/pay:([a-zA-Z0-9]+)/', $description, $matches)) {
                $hash = $matches[1];
                dump($hash);
//                $payment = Payment::where('hash', $hash)->where('status', 'pending')->first();
//                if ($payment) {
//                    $payment->update(['status' => 'paid']);
//                    $this->info("Оплата найдена и обновлена для хеша: {$hash}");
//                }
            }
        }

        return 0;
    }
}
