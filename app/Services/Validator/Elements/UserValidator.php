<?php

namespace App\Services\Validator\Elements;

use App\Models\Car;
use App\Models\CarGene;
use App\Models\CarModel;
use App\Models\City;
use App\Models\Color;
use App\Models\User;
use App\Services\Validator\AbstractValidator;

class UserValidator extends AbstractValidator
{
    protected function check(array $data): array
    {
        // Проверка уникальности телефона
        if (!empty($data['phone']) && User::where('phone', formatPhoneNumber($data['phone']))->exists()) {
            $this->errors[] = 'Номер телефону вже існує.';
        }

        // Проверка наличия городов
        if (!empty($data['cities']) && is_array($data['cities'])) {
            foreach ($data['cities'] as $cityId) {
                if (!City::where('id', $cityId)->exists()) {
                    $this->errors[] = "Місто з ID {$cityId} не знайдено.";
                }
            }
        }

        return $this->errors;

    }
}
