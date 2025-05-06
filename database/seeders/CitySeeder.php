<?php

namespace Database\Seeders;

use App\Models\CarGene;
use App\Models\City;
use App\Models\Color;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            ['id' => 1, 'name' => 'Івано-Франківськ', 'country' => 'Україна', 'latitude' => 48.9226, 'longitude' => 24.7111],
            ['id' => 2, 'name' => 'Київ', 'country' => 'Україна', 'latitude' => 50.4501, 'longitude' => 30.5234],
            ['id' => 3, 'name' => 'Прага', 'country' => 'Чехія', 'latitude' => 50.0755, 'longitude' => 14.4378],
            ['id' => 4, 'name' => 'Дніпро', 'country' => 'Україна', 'latitude' => 48.4647, 'longitude' => 35.0462],
            ['id' => 5, 'name' => 'Запоріжжя', 'country' => 'Україна', 'latitude' => 47.8388, 'longitude' => 35.1396],
            ['id' => 6, 'name' => 'Харків', 'country' => 'Україна', 'latitude' => 49.9935, 'longitude' => 36.2304],
            ['id' => 7, 'name' => 'Львів', 'country' => 'Україна', 'latitude' => 49.8397, 'longitude' => 24.0297],
            ['id' => 8, 'name' => 'Одеса', 'country' => 'Україна', 'latitude' => 46.4825, 'longitude' => 30.7233],
            ['id' => 9, 'name' => 'Переяслав', 'country' => 'Україна', 'latitude' => 50.0666, 'longitude' => 31.4486],
            ['id' => 10, 'name' => 'Херсон', 'country' => 'Україна', 'latitude' => 46.6354, 'longitude' => 32.6169],
            ['id' => 11, 'name' => 'Кременчук', 'country' => 'Україна', 'latitude' => 49.0681, 'longitude' => 33.4204],
            ['id' => 12, 'name' => 'Черкаси', 'country' => 'Україна', 'latitude' => 49.4444, 'longitude' => 32.0598],
            ['id' => 13, 'name' => 'Чернігів', 'country' => 'Україна', 'latitude' => 51.4982, 'longitude' => 31.2893],
            ['id' => 14, 'name' => 'Буча', 'country' => 'Україна', 'latitude' => 50.5539, 'longitude' => 30.2121],
            ['id' => 15, 'name' => 'Краків', 'country' => 'Польща', 'latitude' => 50.0647, 'longitude' => 19.9450],
            ['id' => 16, 'name' => 'Хмельницький', 'country' => 'Україна', 'latitude' => 49.4229, 'longitude' => 26.9871],
            ['id' => 17, 'name' => 'Тернопіль', 'country' => 'Україна', 'latitude' => 49.5535, 'longitude' => 25.5948],
            ['id' => 18, 'name' => 'Вінниця', 'country' => 'Україна', 'latitude' => 49.2331, 'longitude' => 28.4682],
            ['id' => 19, 'name' => 'Луцьк', 'country' => 'Україна', 'latitude' => 50.7472, 'longitude' => 25.3254],
            ['id' => 20, 'name' => 'Кривий Ріг', 'country' => 'Україна', 'latitude' => 47.9105, 'longitude' => 33.3918],
            ['id' => 21, 'name' => 'Бровари', 'country' => 'Україна', 'latitude' => 50.5111, 'longitude' => 30.7902],
            ['id' => 22, 'name' => 'Баришівка', 'country' => 'Україна', 'latitude' => 50.3622, 'longitude' => 31.3193],
            ['id' => 23, 'name' => 'Житомир', 'country' => 'Україна', 'latitude' => 50.2547, 'longitude' => 28.6587],
            ['id' => 24, 'name' => 'Коростень', 'country' => 'Україна', 'latitude' => 50.9609, 'longitude' => 28.6380],
            ['id' => 25, 'name' => 'Ужгород', 'country' => 'Україна', 'latitude' => 48.6208, 'longitude' => 22.2879],
            ['id' => 26, 'name' => 'Кропивницький', 'country' => 'Україна', 'latitude' => 48.5079, 'longitude' => 32.2623],
            ['id' => 27, 'name' => 'Городок', 'country' => 'Україна', 'latitude' => 49.7876, 'longitude' => 23.6402],
            ['id' => 28, 'name' => 'Стрий', 'country' => 'Україна', 'latitude' => 49.2588, 'longitude' => 23.8561],
            ['id' => 29, 'name' => 'Броди', 'country' => 'Україна', 'latitude' => 50.0812, 'longitude' => 25.1503],
            ['id' => 30, 'name' => 'Чорноморськ', 'country' => 'Україна', 'latitude' => 46.3030, 'longitude' => 30.6540],
            ['id' => 31, 'name' => 'Полтава', 'country' => 'Україна', 'latitude' => 49.5883, 'longitude' => 34.5514],
            ['id' => 32, 'name' => 'Миколаїв', 'country' => 'Україна', 'latitude' => 46.9750, 'longitude' => 31.9946],
            ['id' => 33, 'name' => 'Суми', 'country' => 'Україна', 'latitude' => 50.9077, 'longitude' => 34.7981],
            ['id' => 34, 'name' => 'Чернівці', 'country' => 'Україна', 'latitude' => 48.2917, 'longitude' => 25.9352],
            ['id' => 35, 'name' => 'Демид', 'country' => 'Україна', 'latitude' => 50.7060, 'longitude' => 30.1746],
            ['id' => 36, 'name' => 'Біла Церква', 'country' => 'Україна', 'latitude' => 49.7950, 'longitude' => 30.1156],
            ['id' => 37, 'name' => 'Коростишів', 'country' => 'Україна', 'latitude' => 50.3165, 'longitude' => 29.0555],
            ['id' => 38, 'name' => 'США', 'country' => 'США', 'latitude' => 37.0902, 'longitude' => -95.7129],
            ['id' => 39, 'name' => 'Луганськ', 'country' => 'Україна', 'latitude' => 48.5733, 'longitude' => 39.3558],
            ['id' => 40, 'name' => 'Кіпр', 'country' => 'Кіпр', 'latitude' => 35.1264, 'longitude' => 33.4299],
            ['id' => 41, 'name' => 'Чорногорія', 'country' => 'Чорногорія', 'latitude' => 42.7087, 'longitude' => 19.3744],
            ['id' => 42, 'name' => 'Миргород', 'country' => 'Україна', 'latitude' => 49.9649, 'longitude' => 33.6074],
            ['id' => 43, 'name' => 'Сан-Джуліан', 'country' => 'Мальта', 'latitude' => 35.9258, 'longitude' => 14.4894],
        ];

        foreach ($cities as $city) {
            City::updateOrCreate(
                ['id' => $city['id']], // Поиск по ID
                $city // Обновление или создание
            );
        }
    }
}
