<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyTelegramWebhook
{
    public function handle(Request $request, Closure $next)
    {
        $expectedToken = config('services.telegram.webhook_secret');

        if (!$expectedToken) {
            return $next($request); // fallback для dev
        }

        $header = $request->header('x-telegram-bot-api-secret-token');

        if (!$header || !hash_equals($expectedToken, $header)) {

            Log::warning('Invalid Telegram webhook secret', [
                'ip' => $request->ip(),
                'received' => $header,
            ]);

            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
