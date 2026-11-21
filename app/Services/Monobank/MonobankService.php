<?php

namespace App\Services\Monobank;

use App\Eloquent\FinanceEloquent;
use App\Enum\EnumMonoAccount;
use App\Enum\EnumMonoStatus;
use App\Enum\EnumTelegramEvents;
use App\Models\MonoTransaction;
use App\Notifications\Support\TelegramRecipients;
use App\Notifications\UnmatchedMonoPaymentNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class MonobankService
{
    /**
     * Records an incoming jar payment and credits it to the member whose single-use payment link was paid.
     * A payment that can't be attributed to a member is stored without user_id and reported to the admins.
     */
    public function handleStatementItem(EnumMonoAccount $account, array $statementItem, ?string $sourceIp): void
    {
        // Outgoing jar operations (withdrawals) have no payer to attribute.
        if ((int) ($statementItem['amount'] ?? 0) <= 0) {
            return;
        }

        $statementId = $statementItem['id'] ?? null;

        // Monobank redelivers the webhook if it doesn't get 200 in time: process each operation once.
        if ($statementId && MonoTransaction::where('statement_id', $statementId)->exists()) {
            return;
        }

        $hash = preg_match('/pay:([a-zA-Z0-9]+)/', $statementItem['comment'] ?? '', $matches) ? $matches[1] : null;

        $payment = DB::transaction(function () use ($hash, $statementId, $statementItem, $account, $sourceIp): MonoTransaction {
            // A hash is single-use: only a pending row can be confirmed. A reused (shared/bookmarked)
            // link must not credit someone else's payment to the link's original owner.
            $payment = $hash === null ? null : MonoTransaction::where('hash', $hash)
                ->where('status', EnumMonoStatus::PENDING->getAlias())
                ->lockForUpdate()
                ->first();
            $isMatched = $payment !== null;
            $payment ??= new MonoTransaction;

            $payment->statement_id = $statementId;
            $payment->source_ip = $sourceIp;
            $payment->status = EnumMonoStatus::CONFIRMED->getAlias();
            $payment->currency_code = $statementItem['currencyCode'] ?? null;
            $payment->amount = $statementItem['amount'] ?? null;
            $payment->description = $statementItem['description'] ?? null;
            $payment->comment = $statementItem['comment'] ?? null;
            $payment->jar_id = $account->getID();
            $payment->save();

            if ($isMatched) {
                FinanceEloquent::createByMono($payment);
            }

            return $payment;
        });

        if (! $payment->user_id) {
            $usedLink = $hash === null ? null : MonoTransaction::where('hash', $hash)->with('user')->first();

            Notification::send(
                TelegramRecipients::routes(EnumTelegramEvents::MONO_UNMATCHED_PAYMENT->getIds()),
                new UnmatchedMonoPaymentNotification($payment, $hash !== null, $usedLink),
            );
        }
    }

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
                            'webHookUrl' => $webhookUrl,
                        ]);

                    if ($setupResponse->successful()) {
                        Log::info('Monobank: Webhook successfully reinstalled.', ['url' => $webhookUrl]);
                    } else {
                        Log::error('Monobank: Failed to reinstall webhook.', [
                            'status' => $setupResponse->status(),
                            'body' => $setupResponse->body(),
                        ]);
                    }
                } else {
                    Log::info('Monobank: Webhook is active.', ['url' => $activeWebhook]);
                }
            } else {
                // Випадок, якщо токен невірний (403) або банк лежить
                Log::error('Monobank: API client-info request failed.', [
                    'status' => $response->status(),
                    'message' => $response->json('errorDescription') ?? 'Unknown error',
                ]);
            }

        } catch (\Throwable $e) {
            // Ловимо помилки з'єднання, SSL або DNS
            Log::critical('Monobank: Critical error during webhook check.', [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }
}
