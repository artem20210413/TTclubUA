<?php

namespace App\Jobs\Autoria;

use App\Enum\EnumTelegramEvents;
use App\Notifications\AutoriaPackageExhaustedNotification;
use App\Notifications\Support\TelegramRecipients;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Shared handling of AUTO.RIA rejections for the Autoria sync jobs (specs/009-autoria-package-alert).
 * 403 = the developer package is exhausted or the API key is rejected; 429 = the hourly limit.
 */
trait HandlesAutoriaRejections
{
    /**
     * Keeps AUTO.RIA's own error text in the log, so the exact reason is known (FR-007).
     */
    protected function logRejection(Response $response, string $request, string $level = 'warning'): void
    {
        Log::log($level, "AUTO.RIA відхилив запит ({$request}): HTTP {$response->status()}", [
            'body' => Str::limit($response->body(), 1000),
        ]);
    }

    /**
     * A 403 cannot succeed until someone renews the package, so the job fails right away
     * without retries (FR-001, FR-002) and the system-errors chat is alerted (FR-003).
     */
    protected function failAccessDenied(Response $response, string $request): void
    {
        $this->logRejection($response, $request, 'error');
        $this->alertPackageExhausted();
        $this->fail(new RuntimeException("AUTO.RIA відхилив запит ({$request}): HTTP 403"));
    }

    /**
     * At most one alert per calendar day (FR-005). Cache::add is atomic, so when many jobs get
     * 403 at once only one of them sends; a delivery failure never reaches the job (FR-009).
     */
    private function alertPackageExhausted(): void
    {
        if (! Cache::add('autoria:package-exhausted-alert:'.now()->toDateString(), true, now()->addDay())) {
            return;
        }

        try {
            Notification::send(
                TelegramRecipients::routes(EnumTelegramEvents::SYSTEM_ERRORS->getIds()),
                new AutoriaPackageExhaustedNotification,
            );
        } catch (\Throwable $e) {
            Log::error('Не вдалося сповістити про вичерпаний пакет AUTO.RIA: '.$e->getMessage());
        }
    }
}
