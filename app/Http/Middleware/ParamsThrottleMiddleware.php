<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ParamsThrottleMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param int $ttlSeconds  За сколько секунд запрещаем повторять тот же запрос
     */
    public function handle(Request $request, Closure $next, int $ttlSeconds = 60)
    {
        $ip     = $request->ip();
        $method = $request->method();
        $path   = $request->path();

        // Берём только GET-параметры (?a=1&b=2)
        $params = $request->query();

        // Сортируем, чтобы порядок не влиял: ?a=1&b=2 == ?b=2&a=1
        ksort($params);

        // Если параметров нет — всё равно учитываем это как один вариант
        $paramsSignature = empty($params)
            ? 'no_params'
            : sha1(json_encode($params, JSON_UNESCAPED_UNICODE));

        // Ключ кеша: IP + метод + путь + сигнатура параметров
        $cacheKey = "params_throttle:{$ip}:{$method}:{$path}:{$paramsSignature}";

        if (Cache::has($cacheKey)) {
            $blockedUntil = Cache::get($cacheKey); // timestamp (time())
            $retryAfter   = max(0, $blockedUntil - time());

            return response()->json([
                'message'             => 'Такий запит вже був нещодавно. Спробуйте пізніше.',
                'retry_after_seconds' => $retryAfter,
            ], 429);
        }

        // Сохраняем метку времени, когда блок истечёт
        $blockedUntil = time() + $ttlSeconds;

        Cache::put(
            $cacheKey,
            $blockedUntil,
            now()->addSeconds($ttlSeconds)
        );

        return $next($request);
    }
}
