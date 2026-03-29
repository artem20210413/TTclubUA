<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
//        $middleware->validateCsrfTokens(except: [
////            '/*', в web/
//        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'only.ua' => \App\Http\Middleware\OnlyFromUkraine::class,
            'params.throttle' => \App\Http\Middleware\ParamsThrottleMiddleware::class,
            'telegram.webhook' => \App\Http\Middleware\VerifyTelegramWebhook::class,
            'app.attributes' => \App\Http\Middleware\TrackAppAttributesMiddleware::class,
        ]);
//        $middleware->alias(['permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
