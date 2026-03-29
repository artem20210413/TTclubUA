<?php
//
//namespace App\Jobs\Autoria;
//
//use App\Models\ExternalCar;
//use Illuminate\Bus\Queueable;
//use Illuminate\Contracts\Queue\ShouldQueue;
//use Illuminate\Foundation\Bus\Dispatchable;
//use Illuminate\Queue\InteractsWithQueue;
//use Illuminate\Queue\SerializesModels;
//use Illuminate\Support\Facades\Http;
//use Illuminate\Support\Facades\Log;
//
//class SearchCarsJob implements ShouldQueue
//{
//    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
//
//    public $tries = 5;
//    public $backoff = 3600; // 1 час при неудаче
//
//    protected $filters;
//
//    public function __construct(array $filters = [])
//    {
//        $this->filters = array_merge([
//            'category_id' => 1,
//            'marka_id[0]' => 6,
//            'marka_id[1]' => 6,
//            'marka_id[2]' => 6,
//            'countpage' => 200,
//            'model_id[0]' => 1837,
//            'model_id[1]' => 33483,
//            'model_id[2]' => 3452,
//        ], $filters);
//    }
//
//    public function handle()
//    {
//        $apiKey = config('services.autoria.api_key');
//
//        $response = Http::get("https://developers.ria.com/auto/search", array_merge([
//            'api_key' => $apiKey,
//        ], $this->filters));
//
//        if ($response->failed()) {
//            throw new \Exception("Search API failed: " . $response->status());
//        }
//
//        $externalIds = $response->json('result.search_result.ids') ?? [];
//
//        if (empty($externalIds)) {
//            Log::warning("SearchCarsJob: Пошук повернув порожній результат.");
//            return;
//        }
//        ExternalCar::whereIn('external_id', $externalIds)
//            ->update(['is_active' => true]);
//
//        ExternalCar::whereNotIn('external_id', $externalIds)
//            ->update(['is_active' => false]);
//
//        Log::info("Statuses updated. Active: " . count($externalIds));
//
//
//        foreach ($externalIds as $index => $externalId) {
//            // Рассчитываем задержку: первая через 0, вторая через 5 мин, третья через 10 и т.д.
//            $delayMinutes = $index * 3;
//
//            SyncCarDetailJob::dispatch($externalId)
//                ->delay(now()->addMinutes($delayMinutes));
//        }
//    }
//}
namespace App\Jobs\Autoria;

use App\Models\ExternalCar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SearchCarsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 3600;

    protected $filters;
    protected $page;
    protected $offsetItem;
    protected $offsetTimeMinutes = 4;

    public function __construct(array $filters = [], int $page = 0, int $offsetItem = 0)
    {
        $this->page = $page;
        $this->offsetItem = $offsetItem;
        $this->filters = array_merge([
            'category_id' => 1,
            'marka_id[0]' => 6,
            'marka_id[1]' => 6,
            'marka_id[2]' => 6,
            'countpage' => 100,
            'model_id[0]' => 1837,
            'model_id[1]' => 33483,
            'model_id[2]' => 3452,
        ], $filters);
    }

    public function handle()
    {
        $apiKey = config('services.autoria.api_key');

        $response = Http::get("https://developers.ria.com/auto/search", array_merge([
            'api_key' => $apiKey,
            'page' => $this->page, // Додаємо поточну сторінку
        ], $this->filters));

        // Яскрава обробка 429
        if ($response->status() === 429) {
            Log::warning("SearchCarsJob: Отримано 429 (Limit exceeded). Засинаємо на 60 хвилин.");
            // Повертаємо джобу в чергу на пізніше
            return $this->release(now()->addMinutes(60));
        }

        if ($response->failed()) {
            throw new \Exception("Search API невдало: " . $response->status());
        }

        $data = $response->json();
        $externalIds = $data['result']['search_result']['ids'] ?? [];
        $totalFound = $data['result']['search_result']['count'] ?? 0;

        if (empty($externalIds)) {
            Log::info("SearchCarsJob: Сторінка {$this->page} порожня. Завершуємо.");
            return;
        }

        ExternalCar::whereIn('external_id', $externalIds)->update([
            'is_active' => true
        ]);

        $existingIds = ExternalCar::whereIn('external_id', $externalIds)
            ->pluck('external_id')
            ->toArray();

        $newOrExpiredIds = array_diff($externalIds, $existingIds);

        foreach ($newOrExpiredIds as $externalId) {
            SyncCarDetailJob::dispatch($externalId)
                ->delay(now()->addMinutes($this->offsetItem * $this->offsetTimeMinutes));
            $this->offsetItem++;
        }

        Log::info("SearchCarsPage {$this->page}: Оброблено " . count($externalIds) . " of $totalFound");

        // 3. ПЕРЕВІРКА ПАГІНАЦІЇ
        // Розраховуємо, чи є наступна сторінка
        $processedSoFar = ($this->page + 1) * ($this->filters['countpage'] ?? 100);

        if ($processedSoFar < $totalFound) {
            $nextPage = $this->page + 1;
            Log::info("SearchCarsJob: Відправлення наступної сторінки: $nextPage");

            self::dispatch($this->filters, $nextPage, $this->offsetItem)
                ->delay(now()->addSeconds(5));
        } else {
            // Деактивуємо зайві оголошення.
            $deletedCount = ExternalCar::available()
                ->where('updated_at', '<', now()->subMinutes(60))
                ->update(['is_active' => false]);

            // Оновлюємо застарілі оголошення.
            $existingOldIds = ExternalCar::query()->available()->stale(3)->pluck('external_id')->toArray();

            foreach ($existingOldIds as $externalId) {
                SyncCarDetailJob::dispatch($externalId)
                    ->delay(now()->addMinutes($this->offsetItem * $this->offsetTimeMinutes));
                $this->offsetItem++;
            }

            Log::info("SearchCarsJob: Усі сторінки оброблено. Вимкнути: $deletedCount");
        }
    }
}
