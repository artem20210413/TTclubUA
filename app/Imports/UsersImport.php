<?php

namespace App\Imports;

use App\Models\Car;
use App\Models\City;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel
{

//    public function chunkSize(): int
//    {
//        return 100; // Обрабатываем по 100 строк за раз
//    }

    /** @var Collection|City[] */
    private Collection $allCityModel;
    private array $processedData = [];

    public function __construct()
    {
        $this->allCityModel = City::all();
    }


    public function model(array $row)
    {
        if ($row[0] === null || $row[0] === 'Модель') return;

        $licensePlate = $row[3] ?? null;
        $personalizedLicensePlate = $row[4] ?? null;
        $d = $row[7] ?? 0;
        $d = is_numeric($d) ? $d : 0;
        $array['user']['cities'] = $this->searchCities($row[8] ?? null);
        $array['user']['birth_date'] = Date::excelToDateTimeObject($d);
        $array['user']['telegram_nickname'] = $this->filterTelegram($row[6] ?? null); //TODO сохранять только с собачкой при это убирать собачку
        $array['user']['instagram_nickname'] = $row[10] ?? null;
        $array['user']['occupation_description'] = $row[11] ?? null;
        $array['user']['name'] = $row[5] ?? null;
        $array['user']['phone'] = formatPhoneNumber($row[9] ?? null);
        $array['user']['email'] = $array['user']['phone'] . '@gmail.com';

        $array['user']['finances'] = [];
        if ($value = ($row[13] ?? null))
            $array['user']['finances']['2022'] = (int)$value;
        if ($value = ($row[14] ?? null))
            $array['user']['finances']['2023'] = (int)$value;
        if ($value = ($row[15] ?? null))
            $array['user']['finances']['2024'] = (int)$value;
        if ($value = ($row[16] ?? null))
            $array['user']['finances']['2025'] = (int)$value;


        $array['car']['model_id'] = $this->searchCarModel($row[0] ?? null);
        $array['car']['gene_id'] = $this->searchCarGen($row[1] ?? null);
        $array['car']['color_id'] = $this->searchCarColor($row[2] ?? null);
        $array['car']['license_plate'] = $this->generationLicensePlate($licensePlate);
        $array['car']['personalized_license_plate'] = $this->generationLicensePlate($personalizedLicensePlate);

        $this->processedData[] = $array;
    }

    public function createUserAndCar(array $data)
    {
        $user = $this->createUser($data['user']);
        $car = $this->crateCar($user, $data['car']);
    }

    public function createUser(array $userData): ?User
    {
        if (!isset($userData['phone'])) return null;
        /** @var User $user */
        $user = User::where('phone', $userData['phone'])->first();

        if ($user) {
            $user->fill($userData);
        } else {
            $user = new User();
            $user->fill($userData);
            $user->setPassword($userData['phone'] . '!');
            $user->save();
        }

        if (!empty($userData['cities'])) {
            $user->cities()->sync($userData['cities']);
        }

        foreach ($userData['finances'] as $year => $finances) {
            $dataTime = Carbon::createFromDate($year, 7, 1)->startOfDay();
            $user->finances()->create([
                'amount' => $finances,
                'description' => 'З таблиці',
                'created_at' => $dataTime,
            ]);
        }

        return $user;
    }

    public function crateCar(?User $user, array $carData): ?Car
    {
        if (!isset($carData['license_plate']) && !isset($carData['personalized_license_plate'])) return null;
//        if (str_contains($carData['license_plate'], 'ПРОДАВ')) return null;

        $carData['user_id'] = $user?->id;
        $carData['active'] = 1;

        $car = Car::where('license_plate', $carData['license_plate'])->first() ?? Car::Where('personalized_license_plate', $carData['personalized_license_plate'])->first() ?? new Car();

        $car->fill($carData);
        $car->save();

        return $car;
    }


    public function getProcessedData(): array
    {
        return $this->processedData;
    }

    private function createCar()
    {

    }

    private function searchCities(?string $txt): array
    {
        $res = [];

        foreach ($this->allCityModel as $city) {
            if (strpos($txt, $city->name) !== false)
                $res[] = $city->id;
        }

        return $res;
    }

    private function searchCarModel(?string $txt): int
    {
        $txt = trim($txt);
        return match ($txt) {
            'TT' => 1,
            'TTS' => 2,
            'TTRS' => 3,
            default => 1,
        };
    }

    private function searchCarGen(?string $txt): int
    {
        $txt = trim($txt);
        return match ($txt) {
            'MK1' => 1,
            'MK2' => 2,
            'MK3' => 3,
            'MK1 Роадстер' => 4,
            'MK2 Роадстер' => 5,
            'MK3 Роадстер' => 6,
            default => 1,
        };
    }

    private function generationLicensePlate(?string $txt): ?string
    {
        $txt = trim($txt);

        return match ($txt) {
            'ПРОДАВ' => null,
            'ТРЕБА НОМЕР' => null,
            '' => null,
            default => formatNormalizePlateNumber($txt),
        };
    }

    private function searchCarColor(?string $txt): ?int
    {

        $text = trim($txt);
        $keywords = [
            'Білий' => 9,
            'Чорна' => 1,
            'Сіра' => 11,
            'Жовта' => 8,
            'Помаранчева' => 7,
            'Синя' => 2,
            'Червона' => 5,
            'Зелена' => 3,
            'Рожева' => 6,
            'Голуба' => 2,
            'Золота' => 8,
            'Коричнева' => 10,
        ];

        foreach ($keywords as $word => $code) {
            if (mb_stripos($text, $word) !== false) {
                return $code;
            }
        }

        return null;
//        return match ($txt) {
//            'Чорна Ч' => 1,
//            'Синя С' => 2,
//            'Голуба Г' => 2,
//            'Зелена З' => 3,
//            'Червона Ч' => 5,
//            'Рожева Р' => 6,
//            'Помаранчева П' => 7,
//            'Жовта Ж ' => 8,
//            'Білий Б' => 9,
//            'Сіра С' => 11,
//            default => null,
//        };

    }

    private function filterTelegram(?string $tg): ?string
    {
        if (!$tg) return null;

        $list = explode('@', $tg);

        if (count($list) < 2) return null;

        return $list[1];
    }
}
