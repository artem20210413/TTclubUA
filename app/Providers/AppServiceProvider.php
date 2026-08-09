<?php

namespace App\Providers;

use App\Services\Digest\Contracts\DigestSummarizer;
use App\Services\Digest\GeminiDigestSummarizer;
use Illuminate\Support\ServiceProvider;
use Telegram\Bot\Api;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Api::class, fn () => new Api(config('services.telegram.token')));
        $this->app->bind(DigestSummarizer::class, GeminiDigestSummarizer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
