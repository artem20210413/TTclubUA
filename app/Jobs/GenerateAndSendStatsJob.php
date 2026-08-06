<?php

namespace App\Jobs;

use App\Enum\EnumTelegramEvents;
use App\Notifications\AdHocMessageNotification;
use App\Notifications\Support\TelegramMessagePayload;
use App\Notifications\Support\TelegramRecipients;
use App\Services\Gemini\GeminiService;
use App\Services\Gemini\Prompt\Prompt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class GenerateAndSendStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 7; // всего 3 попытки (1 + 2 повтора)

    /**
     * Массив с задержками в секундах между попытками
     * Первая повторная попытка через 600 секунд (10 минут)
     * Вторая повторная попытка через 3600 секунд (60 минут)
     */
    public function backoff(): array
    {
        return [
            60,    // 1 мин
            600,   // 10 мин
            1800,  // 30 мин
            3600,  // 1 час
            10800, // 3 часа
            21600,  // 6 часов (все остальные попытки с 6-й по 20-ю будут раз в 6 часов)
        ];
    }

    public function __construct(
        protected string $periodKey,
        protected array $vars
    ) {}

    public function handle(): void
    {
        $textKey = "{$this->periodKey}_text";

        // --- ШАГ 1: ГЕНЕРАЦІЯ ТЕКСТУ (З перевіркою кешу) ---
        //        $finalText = Cache::get($textKey);
        $finalText = null;

        if (! $finalText) {
            try {
                Log::info("Запит до Gemini для періоду: {$this->periodKey}");

                $prompt = Prompt::buildStatisticsMentionPrompt($this->vars);
                $finalText = GeminiService::generate($prompt)->getText();

                if (! $finalText) {
                    throw new \Exception('Gemini повернув порожній відповідь');
                }

                // Зберігаємо згенерований текст у кеш на 3 дні, щоб не втратити при падінні ТГ
                Cache::put($textKey, $finalText, now()->addDays(3));

            } catch (\Throwable $e) {
                Log::warning('Gemini лежить. Відкладаємо задачу. Помилка: '.$e->getMessage());
                throw $e;
                // Пробуємо через 15 хвилин. Важкі SQL запити повторно НЕ викликаються!
                //                return $this->release(now()->addMinutes(15));
            }
        }

        // --- ШАГ 2: ОТПРАВКА В ТЕЛЕГРАМ ---
        try {
            Log::info('Надсилання тексту статистики в Telegram...');

            Notification::send(
                TelegramRecipients::routes(EnumTelegramEvents::STATS_MENTION->getIds()),
                new AdHocMessageNotification(new TelegramMessagePayload(text: $finalText, disableWebPagePreview: true))
            );

            // Видаляємо вже непотрібний текст з кешу
            Cache::forget($textKey);

        } catch (\Throwable $e) {
            Log::error('Помилка відправки в Telegram: '.$e->getMessage());

            throw $e;
        }
    }
}
