<?php

namespace Database\Seeders;

use App\Models\CarGene;
use App\Models\CarModel;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CarModelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'TT'],
            ['name' => 'TTS'],
            ['name' => 'TTRS'],
        ];

        foreach ($roles as $role) {
            CarModel::firstOrCreate(
                ['name' => $role['name']] // если не нашли, создаем с указанным guard_name
            );
        }
    }
}
