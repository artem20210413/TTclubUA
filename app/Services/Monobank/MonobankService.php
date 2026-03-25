<?php

namespace App\Services\Monobank;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MonobankService
{

//    public function checkWebhook()
//    {
//        Log::info('Monobank webhook check');
//        $currentInfo = Http::withHeaders([
//            'X-Token' => config('services.monobank.token')
//        ])->get('https://api.monobank.ua/personal/client-info');
//
//        if ($currentInfo->successful()) {
//            $webHookUrl = $currentInfo->json('webHookUrl');
//
//            if (empty($webHookUrl)) {
//                Http::withHeaders([
//                    'X-Token' => config('services.monobank.token')
//                ])->post('https://api.monobank.ua/personal/webhook', [
//                    'webHookUrl' => 'https://ttclub.com.ua/api/monobank/webhook'
//                ]);
//
//                Log::warning('Monobank. Webhook was empty and has been reinstalled.');
//            }
//        }
//    }
    public function checkWebhook()
    {
        $token = config('services.monobank.token');
        $webhookUrl = 'https://ttclub.com.ua/api/webhook/monobank';

        Log::info('Monobank: Starting webhook verification...');

        try {
            $response = Http::withHeaders(['X-Token' => $token])
                ->timeout(10) // Додаємо таймаут, щоб джоба не зависла
                ->get('https://api.monobank.ua/personal/client-info');

            if ($response->successful()) {
                $activeWebhook = $response->json('webHookUrl');

                if (empty($activeWebhook)) {
                    Log::warning('Monobank: Webhook is MISSING. Attempting to reinstall...');

                    $setupResponse = Http::withHeaders(['X-Token' => $token])
                        ->post('https://api.monobank.ua/personal/webhook', [
                            'webHookUrl' => $webhookUrl
                        ]);

                    if ($setupResponse->successful()) {
                        Log::info('Monobank: Webhook successfully reinstalled.', ['url' => $webhookUrl]);
                    } else {
                        Log::error('Monobank: Failed to reinstall webhook.', [
                            'status' => $setupResponse->status(),
                            'body' => $setupResponse->body()
                        ]);
                    }
                } else {
                    Log::info('Monobank: Webhook is active.', ['url' => $activeWebhook]);
                }
            } else {
                // Випадок, якщо токен невірний (403) або банк лежить
                Log::error('Monobank: API client-info request failed.', [
                    'status' => $response->status(),
                    'message' => $response->json('errorDescription') ?? 'Unknown error'
                ]);
            }

        } catch (\Throwable $e) {
            // Ловимо помилки з'єднання, SSL або DNS
            Log::critical('Monobank: Critical error during webhook check.', [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }

}
