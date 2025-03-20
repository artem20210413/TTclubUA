<?php

namespace Database\Seeders;

use App\Models\PublicationType;
use Illuminate\Database\Seeder;

class PublicationTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $arr = [
            ['id' => 1, 'name' => 'Події', 'description' => '-', 'sort' => 10],
            ['id' => 2, 'name' => 'Події загальні', 'description' => '-', 'sort' => 20],
            ['id' => 3, 'name' => 'Мерч', 'description' => '-', 'sort' => 30],
            ['id' => 4, 'name' => 'Новини', 'description' => '-', 'sort' => 40],
            ['id' => 5, 'name' => 'Партнери', 'description' => '-', 'sort' => 50],
            ['id' => 6, 'name' => 'Збори', 'description' => '-', 'sort' => 60],
        ];

        foreach ($arr as $el) {
            PublicationType::updateOrCreate(
                ['id' => $el['id']], // Поиск по ID
                $el // Обновление или создание
            );
        }
    }
}
