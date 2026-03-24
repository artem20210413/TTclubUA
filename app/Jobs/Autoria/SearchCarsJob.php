<?php
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

    public $tries = 5;
    public $backoff = 3600; // 1 час при неудаче

    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = array_merge([
            'category_id' => 1,
            'marka_id[0]' => 6,
            'countpage'   => 200,
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
        ], $this->filters));

        if ($response->failed()) {
            throw new \Exception("Search API failed: " . $response->status());
        }

        $externalIds = $response->json('result.search_result.ids') ?? [];

        if (empty($externalIds)) {
            Log::warning("SearchCarsJob: Пошук повернув порожній результат.");
            return;
        }
        ExternalCar::whereIn('external_id', $externalIds)
        ->update(['is_active' => true]);

        ExternalCar::whereNotIn('external_id', $externalIds)
            ->update(['is_active' => false]);

        Log::info("Statuses updated. Active: " . count($externalIds));


        foreach ($externalIds as $index => $externalId) {
            // Рассчитываем задержку: первая через 0, вторая через 5 мин, третья через 10 и т.д.
            $delayMinutes = $index * 3;

            SyncCarDetailJob::dispatch($externalId)
                ->delay(now()->addMinutes($delayMinutes));
        }
    }
}
