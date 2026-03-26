<?php

namespace App\Jobs\Autoria;

use App\Models\Car;
use App\Models\ExternalCar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncCarDetailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 3600; // 60 минут при неудаче

    protected $externalId;

    public function __construct($externalId)
    {
        $this->externalId = $externalId;
    }

    public function handle()
    {
        $apiKey = config('services.autoria.api_key');

        $response = Http::get("https://developers.ria.com/auto/info", [
            'api_key' => $apiKey,
            'auto_id' => $this->externalId,
        ]);

        if ($response->status() === 429) {
            // Если получили "Too Many Requests", возвращаем в очередь через 10 минут
            return $this->release(600);
        }

        if ($response->failed()) {
            Log::error("AUTO.RIA API Error", [
                'id' => $this->externalId,
                'status' => $response->status(),
                'body' => $response->body() // Тут ми побачимо реальну причину від RIA
            ]);

            throw new \Exception("Detail API failed for ID {$this->externalId}");
        }

        $data = $response->json();

        $plateNumber = isset($data['plateNumber']) ? formatNormalizePlateNumber($data['plateNumber']) : null;
        $plateNumber = $plateNumber == '' ? null : $plateNumber;

        if ($plateNumber) {
            $car = Car::query()->where('license_plate', $plateNumber)->first();
            $user_id = $car?->user_id ?? null;
        }
        ExternalCar::updateOrCreate(
            ['external_id' => $data['autoData']['autoId']],
            [
                'plate_number' => $plateNumber,
                'vin' => $data['VIN'] ?? null,
                'title' => $data['title'] ?? null,
                'price_usd' => $data['USD'] ?? null,
                'city_name' => $data['locationCityName'] ?? null,
                'mark_name' => $data['markName'] ?? null,
                'model_name' => $data['modelName'] ?? null,
                'sub_category' => $data['subCategoryName'] ?? null,
                'color_hex' => $data['color']['hex'] ?? null,
                'year' => $data['autoData']['year'] ?? null,
                'is_active' => $data['autoData']['active'] ?? true,
                'is_sold' => $data['autoData']['isSold'] ?? false,
                'raw_data' => $this->clearData($data),
                'user_id' => $user_id ?? null,
            ]
        );

        Log::info("Car synced: " . $this->externalId);
    }

    protected function clearData(array $data): array
    {
        // 1. Видаляємо важкі та непотрібні візуальні елементи
        unset($data['vinSvg']);           // Величезний рядок з кодом SVG
        unset($data['codedVin']);        // Зашифрований VIN (у вас уже є відкритий)
        unset($data['secureKey']);       // Службовий ключ для сайту
        unset($data['badges']);          // Масив іконок (краще реалізувати логіку на Flutter)

        // 2. Видаляємо службові дані про рівні оголошення та рекламу
        unset($data['levelData']);
        unset($data['userBlocked']);
        unset($data['optionStyles']);
        unset($data['userHideADSStatus']);
        unset($data['withInfoBar']);
        unset($data['autoInfoBar']);      // Якщо не плануєте виводити плашки RIA

        // 3. Видаляємо дані про дилерів та партнерів, якщо вони пусті або не потрібні
        if (isset($data['dealer']) && empty($data['dealer']['name'])) {
            unset($data['dealer']);
        }
        unset($data['partnerId']);

        // 5. Очищуємо об'єкт стану (stateData), залишаємо тільки назви
        if (isset($data['stateData'])) {
            unset($data['stateData']['linkToCatalog']);
            unset($data['stateData']['title']);
        }

        // 6. Очищуємо деталі перевірок, якщо вони не використовуються
        unset($data['checkedVin']['isShow']);
        unset($data['checkedVin']['orderId']);

        return $data;
    }
}
