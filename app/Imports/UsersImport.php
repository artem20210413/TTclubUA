<?php

namespace App\Imports;

use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class UsersImport implements ToModel
{
    /** @var Collection|City[] */
    private Collection $allCityModel;

    public function __construct()
    {
        $this->allCityModel = City::all();
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $userArray['cities'] = $this->searchCities($row[8] ?? null);//TODO
        $userArray['birth_date'] = Date::excelToDateTimeObject($row[7] ?? 0);
        $userArray['telegram_nickname'] = $row[6] ?? null;
        $userArray['instagram_nickname'] = $row[10] ?? null;
        $userArray['occupation_description'] = $row[11] ?? null;
        $userArray['name'] = $row[5] ?? null;
        $userArray['phone'] = $row[9] ?? null;
        $userArray['email'] = $userArray['phone'] . '@gmail.com';

        $carArray['madel_id'] = $this->searchCarModel($row[0] ?? null);
        $carArray['gene_id'] = $this->searchCarGen($row[1] ?? null);
        $carArray['color_id'] = $this->searchCarColor($row[2] ?? null);
        $carArray['license_plate'] = $this->generationLicensePlate($row[3] ?? null); //TODO ПРОДАВ
        $carArray['personalized_license_plate'] = $this->generationLicensePlate($row[4] ?? null); //TODO


        $user = $this->createUser($userArray);
        dd($user, $row, $userArray);

    }

    private function createUser(array $userData): ?User
    {
        if (!isset($userData['phone'])) return null;

        $user = User::where('phone', formatPhoneNumber($userData['phone']))->first();

        if ($user) {
            $user->update($userData);
        } else {
            $user = new User();
            $user->fill($userData);
            $user->setPassword($userData['phone'] . '!');
            $user->setPhone($userData['phone']);
            $user->save();
        }

        if (!empty($userArray['cities'])) {
            $user->cities()->sync($userArray['cities']);
        }

        return $user;
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

    private function generationLicensePlate(?string $txt): string
    {
        $txt = trim($txt);

        return match ($txt) {
            'ПРОДАВ' => $txt,
            default => $txt,  //TODO convert LicensePlate in latin characters
        };
    }

    private function searchCarColor(?string $txt): int
    {
        $txt = trim($txt);
        return match ($txt) {
            'Чорна Ч' => 1,
            'Синя С' => 2,
            'Голуба Г' => 2,
            'Зелена З' => 3,
            'Червона Ч' => 5,
            'Рожева Р' => 6,
            'Помаранчева П' => 7,
            'Жовта Ж ' => 8,
            'Білий Б' => 9,
            'Сіра С' => 11,
            default => null,
        };

    }
}
