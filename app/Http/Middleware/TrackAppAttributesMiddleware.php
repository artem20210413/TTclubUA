<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TrackAppAttributesMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {

            $user = $request->user();
            $version = $request->header('X-App-Version');
            $platform = $request->header('X-App-Platform');

            // Перевіряємо, чи юзер авторизований і чи є хедер з версією
            if ($user && ($version || $platform)) {
                $token = $user->currentAccessToken();

                // Оновлюємо тільки якщо версія змінилася (щоб не робити зайвий SQL запит щоразу)
                $hasChanged = ($token->app_version !== $version) || ($token->app_platform !== $platform);

                if ($hasChanged) {
                    $token->forceFill([
                        'app_version' => $version,
                        'app_platform' => $platform,
                    ])->save();
                }
            }
        } catch (\Throwable $exception) {
            Log::error("Failed to update app version in Middleware: " . $exception->getMessage(), [
                'user_id' => auth()->id(),
                'version_header' => $request->header('X-App-Version'),
                'trace' => $exception->getTraceAsString()
            ]);
        }

        return $next($request);
    }
}
