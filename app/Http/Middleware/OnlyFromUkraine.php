<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Stevebauman\Location\Facades\Location;

class OnlyFromUkraine
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        // Можно учитывать Cloudflare или прокси
        if ($request->header('CF-Connecting-IP')) {
            $ip = $request->header('CF-Connecting-IP');
        }

        $position = Location::get($ip);
//        dump($ip, $position);
        if (app()->environment('local')) {
            return $next($request);
        }

        if ($position && $position->countryCode === 'UA') {
            return $next($request);
        }

        // Можно вернуть кастомное сообщение
        abort(403, 'Доступ дозволено лише з України 🇺🇦');
    }
}
