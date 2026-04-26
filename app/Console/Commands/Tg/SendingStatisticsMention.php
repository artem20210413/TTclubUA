<?php

namespace App\Console\Commands\Tg;

use App\Eloquent\MentionEloquent;

// Убедитесь, что путь верный
use App\Enum\EnumTelegramEvents;
use App\Models\Car;
use App\Services\Gemini\GeminiService;
use App\Services\Gemini\Prompt\Prompt;
use App\Services\Telegram\TelegramBot;
use App\Services\Telegram\TelegramBotHelpers;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendingStatisticsMention extends Command
{
    /**
     * Имя и подпись команды в консоли.
     * Запуск: php artisan tg:send-stats
     */
    protected $signature = 'tg:send-stats-mention';

    /**
     * Описание команды.
     */
    protected $description = 'Генерация и отправка ежемесячной статистики по упоминаниям (Фа-Фа) в Telegram';

    /**
     * Выполнение команды.
     */
    public function handle()
    {
        $this->info('Начинаю збір статистики за минулий місяць...');


        // 1. Определяем период (прошлый месяц)
        $periodStart = now()->subMonth()->startOfMonth();
        $periodEnd = now()->subMonth()->endOfMonth();
        $monthName = $periodStart->translatedFormat('F'); // Для логов или заголовка


        try {

            $activeDay = MentionEloquent::getMostActiveDay($periodStart, $periodEnd);
            $topColor = MentionEloquent::getMostSpottedColor($periodStart, $periodEnd);
            $total = MentionEloquent::getTotalMentions($periodStart, $periodEnd);
            $totalAll = MentionEloquent::getTotalAllMentions();
            $topCars = MentionEloquent::getTopCars($periodStart, $periodEnd, 1);
            $topHunters = MentionEloquent::getTopHunters($periodStart, $periodEnd, 3);


            $grouped = $topCars->groupBy('mentions_count')
                ->sortKeysDesc() // Сортуємо: спочатку найбільша кількість (ТОП-1)
                ->values();     // Скидаємо ключі, щоб отримати масив масивів за порядком [0, 1, 2]
            $resultCars = $grouped->map(function ($placeGroup) {
                $uniqueCount = $placeGroup->first()->mentions_count;
                return [
                    'count' => $uniqueCount,
                    'cars' => $placeGroup->map(function ($car) {
                        $user = $car->user;
                        return [
                            'info' => $car->getGeneralShortInfo(),
                            'user' => [
                                'name' => $user->name,
                                'mark_tg' => TelegramBotHelpers::TryMentionPerson($user) ?? '—',
                            ],

                        ];
                    })->toArray()
                ];
            })->toArray();
            $topCarList = !empty($resultCars) ? json_encode($resultCars, JSON_UNESCAPED_UNICODE) : null;

            $grouped = $topHunters->groupBy('unique_cars_count')
                ->sortKeysDesc() // Сортуємо: спочатку найбільша кількість (ТОП-1)
                ->values();     // Скидаємо ключі, щоб отримати масив масивів за порядком [0, 1, 2]
            $resultHunters = $grouped->map(function ($placeGroup) {
                // Оскільки в групі всі мають однакову кількість unique_cars_count, беремо у першого
                $uniqueCount = $placeGroup->first()->unique_cars_count;

                return [
                    'unique_count' => $uniqueCount,
                    'winners' => $placeGroup->map(function ($user) {
                        return [
                            'name' => $user->name,
                            'mark_tg' => TelegramBotHelpers::TryMentionPerson($user) ?? '—',
                            'total_mentions_count' => $user->total_mentions_count,
                        ];
                    })->toArray()
                ];
            })->toArray();
            $topHunterList = !empty($resultHunters) ? json_encode($resultHunters, JSON_UNESCAPED_UNICODE) : null;
// 3. Масив змінних для заміни

            $vars = [
                'MONTH_NAME' => $monthName,
                'TOTAL_ALL_TIME' => $totalAll,
                'TOTAL_MONTH' => $total,
                'MOST_ACTIVE_DAY' => $activeDay,
                'TOP_CAR_DATA' => $topCarList ?: 'Поки ніхто не потрапив у кадр',
                'TOP_HUNTER_DATA' => $topHunterList ?: 'Мисливці в засідці',
                'MOST_SPOTTED_COLOR' => $topColor?->name ?? 'невизначений',
                'COLOR_COUNT' => $topColor?->mentions_count ?? 0,
            ];

            $this->info('Начинаю генерацію статистики...');
            $finalText = GeminiService::generate(Prompt::buildStatisticsMentionPrompt($vars))->getText();
//        $botT = new TelegramBot(EnumTelegramEvents::TEST);
            $botT = new TelegramBot(EnumTelegramEvents::STATS_MENTION);
            $botT->sendMessage($finalText, disableWebPagePreview: true);

            $this->info('Статистика успешно отправлена в Telegram!');


        } catch (\Exception $e) {
            Log::error("Ошибка при отправке статистики ТТ Клуба: " . $e->getMessage());
            $this->error("Что-то пошло не так. Проверь логи.");
        }
    }
//    public function handle()
//    {
//        $this->info('Начинаю сбор статистики за прошлый месяц...');
//
//
//        // 1. Определяем период (прошлый месяц)
//        $period = now()->subMonth()->startOfMonth();
//        $monthName = $period->translatedFormat('F'); // Для логов или заголовка
//
//        try {
//            // 2. Сбор данных через Eloquent слои
//            $topCars = MentionEloquent::getTopCars($period);
//            $topHunter = MentionEloquent::getTopHunter($period);
//            $topColor = MentionEloquent::getMostSpottedColor($period);
//            $activeDay = MentionEloquent::getMostActiveDay($period);
//            $total = MentionEloquent::getTotalMentions($period);
//
//            $topCar_1 = $topCars->get(0);
//            $topCar_2 = $topCars->get(1);
//            $topCar_3 = $topCars->get(2);
//
//            $templateText = config('telegram.messages.stats_mention', '---');
//            /**
//             * @param Car $topCar_1
//             * @param Car $topCar_2
//             * @param Car $topCar_3
//             */
//
//            $vars = [
//                '{TOTAL_MENTIONS}' => $total,
//                '{MOST_ACTIVE_DAY}' => $activeDay,
//
//                '{TOP_CAR_1_NAME}' => $topCar_1 ? 'Audi ' . $topCar_1->getGeneralShortInfo() : 'TT',
//                '{TOP_CAR_1_OWNER}' => TelegramBotHelpers::TryMentionPerson($topCar_1->user) ?? '—',
//                '{TOP_CAR_1_COUNT}' => $topCar_1?->mentions_count ?? 0,
//
//                '{TOP_CAR_2_NAME}' => $topCar_2 ? 'Audi ' . $topCar_2->getGeneralShortInfo() : 'TT',
//                '{TOP_CAR_2_OWNER}' => TelegramBotHelpers::TryMentionPerson($topCar_2->user) ?? '—',
//                '{TOP_CAR_2_COUNT}' => $topCar_2?->mentions_count ?? 0,
//
//                '{TOP_CAR_3_NAME}' => $topCar_3 ? 'Audi ' . $topCar_3->getGeneralShortInfo() : 'TT',
//                '{TOP_CAR_3_OWNER}' => TelegramBotHelpers::TryMentionPerson($topCar_3->user) ?? '—',
//                '{TOP_CAR_3_COUNT}' => $topCar_3?->mentions_count ?? 0,
//
//                '{TOP_HUNTER}' => TelegramBotHelpers::TryMentionPerson($topHunter),
//                '{TOP_HUNTER_COUNT}' => $topHunter?->mentions_count ?? 0,
//
//                '{MOST_SPOTTED_COLOR}' => $topColor?->name ?? 'Невідомо',
//                '{COLOR_COUNT}' => $topColor?->mentions_count ?? 0,
//            ];
//
//
//            $finalText = str_replace(array_keys($vars), array_values($vars), $templateText);
//
//            $botT = new TelegramBot(EnumTelegramEvents::STATS_MENTION);
//            $botT->sendMessage($finalText, disableWebPagePreview: true);
//
//            $this->info('Статистика успешно отправлена в Telegram!');
//
//
//        } catch (\Exception $e) {
//            Log::error("Ошибка при отправке статистики ТТ Клуба: " . $e->getMessage());
//            $this->error("Что-то пошло не так. Проверь логи.");
//        }
//    }
}
