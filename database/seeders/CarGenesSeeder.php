<?php

namespace Database\Seeders;

use App\Models\CarGene;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CarGenesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'MK1'],
            ['name' => 'MK2'],
            ['name' => 'MK3'],
            ['name' => 'MK1 Roadster'],
            ['name' => 'MK2 Roadster'],
            ['name' => 'MK3 Roadster'],
        ];

        foreach ($roles as $role) {
            CarGene::firstOrCreate(
                ['name' => $role['name']] // если не нашли, создаем с указанным guard_name
            );
        }
    }
}
