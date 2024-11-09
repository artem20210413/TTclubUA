<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'guard_name' => 'web'],
            ['name' => 'editor', 'guard_name' => 'web'],
            ['name' => 'user', 'guard_name' => 'web']
        ];

        foreach ($roles as $role) {
            // Создаем роль только если она не существует
            Role::firstOrCreate(
                ['name' => $role['name']], // ищем роль по имени
                ['guard_name' => $role['guard_name']] // если не нашли, создаем с указанным guard_name
            );
        }
    }
}
