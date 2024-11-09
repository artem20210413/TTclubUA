<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'create_user', 'guard_name' => 'web'],
        ];

        foreach ($roles as $role) {
            // Создаем роль только если она не существует
            Permission::firstOrCreate(
                ['name' => $role['name']], // ищем роль по имени
                ['guard_name' => $role['guard_name']] // если не нашли, создаем с указанным guard_name
            );
        }
    }
}
