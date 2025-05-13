<?php

namespace App\Services\Validator\Elements;

use App\Models\Car;
use App\Models\CarGene;
use App\Models\CarModel;
use App\Models\Color;
use App\Services\Validator\AbstractValidator;

class CarsValidator extends AbstractValidator
{
    protected function check(array $data): array
    {

        foreach ($data['cars'] ?? [] as $index => $car) {
            if (!CarModel::where('id', $car['model_id'])->exists()) {
                $this->errors[] = "Авто [{$index}]: Модель ID {$car['model_id']} не знайдена.";
            }

            if (!CarGene::where('id', $car['gene_id'])->exists()) {
                $this->errors[] = "Авто [{$index}]: Покоління ID {$car['gene_id']} не знайдено.";
            }

            if (!Color::where('id', $car['color_id'])->exists()) {
                $this->errors[] = "Авто [{$index}]: Колір ID {$car['color_id']} не знайдено.";
            }

            if (!empty($car['license_plate']) && Car::where('license_plate', formatNormalizePlateNumber($car['license_plate']))->exists()) {
                $this->errors[] = "Авто [{$index}]: Номер {$car['license_plate']} вже використовується.";
            }
        }

        return $this->errors;
    }
}
