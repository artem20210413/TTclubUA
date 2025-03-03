<?php

namespace Database\Seeders;

use App\Models\CarGene;
use App\Models\Color;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colors = [
            ['id' => 1, 'name' => 'Чорний', 'hex' => '#000000'],
            ['id' => 2, 'name' => 'Синій', 'hex' => '#0000FF'],
            ['id' => 3, 'name' => 'Зелений', 'hex' => '#008000'],
            ['id' => 4, 'name' => 'Бежевий', 'hex' => '#F5F5DC'],
            ['id' => 5, 'name' => 'Червоний', 'hex' => '#FF0000'],
            ['id' => 6, 'name' => 'Фіолетовий', 'hex' => '#800080'],
            ['id' => 7, 'name' => 'Помаранчевий', 'hex' => '#FFA500'],
            ['id' => 8, 'name' => 'Жовтий', 'hex' => '#FFFF00'],
            ['id' => 9, 'name' => 'Білий', 'hex' => '#FFFFFF'],
            ['id' => 10, 'name' => 'Коричневий', 'hex' => '#A52A2A'],
            ['id' => 11, 'name' => 'Сірий', 'hex' => '#808080'],
            ['id' => 12, 'name' => 'Комбінований', 'hex' => '#000000'],
        ];

        foreach ($colors as $color) {
            Color::updateOrCreate(
                ['id' => $color['id']], // Поиск по ID
                $color // Обновление или создание
            );
        }
    }
}
