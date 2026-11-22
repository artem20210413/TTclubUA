<?php

namespace App\Console\Commands\Tg;

use Illuminate\Console\Command;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\WebhookInfo;

/**
 * Реєструє вебхук бота з secret_token і списком allowed_updates з конфігу.
 * Замість `telegram:webhook --setup` із SDK, який не передає secret_token
 * (після нього VerifyTelegramWebhook відповідав би 403 на кожне оновлення).
 */
class SetWebhook extends Command
{
    public const WEBHOOK_PATH = '/api/telegram/webhook';

    protected $signature = 'tg:set-webhook
        {--url= : URL вебхука (за замовчуванням — поточний URL вебхука або APP_URL + /api/telegram/webhook)}
        {--info : Лише показати поточні налаштування вебхука}';

    protected $description = 'Register the Telegram webhook with the secret token and the allowed update types from config';

    public function handle(Api $telegram): int
    {
        $current = $telegram->getWebhookInfo();
        $this->showInfo('Зараз', $current);

        if ($this->option('info')) {
            return self::SUCCESS;
        }

        $secret = (string) config('services.telegram.webhook_secret');
        if ($secret === '') {
            $this->error('TELEGRAM_WEBHOOK_SECRET не задано: без секрету вебхук прийматиме запити від будь-кого. Вебхук не змінено.');

            return self::FAILURE;
        }

        $url = $this->option('url')
            ?: ($current->get('url') ?: rtrim((string) config('app.url'), '/').self::WEBHOOK_PATH);

        if (! str_starts_with($url, 'https://')) {
            $this->error("URL вебхука має починатися з https://: {$url}");

            return self::FAILURE;
        }

        $allowedUpdates = (array) config('telegram.bots.mybot.allowed_updates');
        if ($allowedUpdates === []) {
            // Напр., застарілий кеш конфігу, зібраний до появи списку.
            $this->error('telegram.bots.mybot.allowed_updates порожній (застарілий кеш конфігу? php artisan config:clear). Вебхук не змінено.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->line('Буде встановлено:');
        $this->line("  url: {$url}");
        $this->line('  secret_token: з TELEGRAM_WEBHOOK_SECRET');
        $this->line('  allowed_updates: '.implode(', ', $allowedUpdates));

        // Типи, які зараз приходять, а після зміни перестануть.
        $dropped = array_diff($this->allowedUpdatesOf($current), $allowedUpdates);
        if ($dropped !== []) {
            $this->warn('Перестануть приходити: '.implode(', ', $dropped));
        }
        if ($current->get('url') && $current->get('url') !== $url) {
            $this->warn("URL зміниться: {$current->get('url')} → {$url}");
        }

        if (! $this->confirm('Зареєструвати вебхук із цими параметрами?', true)) {
            $this->line('Скасовано. Вебхук не змінено.');

            return self::SUCCESS;
        }

        $telegram->setWebhook([
            'url' => $url,
            'secret_token' => $secret,
            'allowed_updates' => $allowedUpdates,
        ]);

        $this->showInfo('Після зміни', $telegram->getWebhookInfo());

        return self::SUCCESS;
    }

    private function showInfo(string $title, WebhookInfo $info): void
    {
        $this->info("{$title}:");
        $this->line('  url: '.($info->get('url') ?: '—'));
        $allowedUpdates = $this->allowedUpdatesOf($info);
        $this->line('  allowed_updates: '.($allowedUpdates ? implode(', ', $allowedUpdates) : 'не задано (типовий набір Telegram, без chat_member)'));
        $this->line('  pending_update_count: '.($info->get('pending_update_count') ?? 0));
        if ($info->get('last_error_message')) {
            $this->warn('  last_error_message: '.$info->get('last_error_message'));
        }
    }

    /** SDK загортає вкладений масив у колекцію (TelegramObject) — приводимо до звичайного списку. */
    private function allowedUpdatesOf(WebhookInfo $info): array
    {
        return collect($info->get('allowed_updates') ?? [])->values()->all();
    }
}
