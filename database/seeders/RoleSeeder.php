<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
            ['name' => 'user', 'guard_name' => 'web'],
            ['name' => 'copywriter', 'guard_name' => 'web'],
            ['name' => 'head-copywriter', 'guard_name' => 'web'],
        ];

        foreach ($roles as $role) {
            // Создаем роль только если она не существует
            $role = Role::firstOrCreate(
                ['name' => $role['name']], // ищем роль по имени
                ['guard_name' => $role['guard_name']] // если не нашли, создаем с указанным guard_name
            );

            if ($role->name === 'admin') {
                $role->givePermissionTo(Permission::all());
                //                $editor->givePermissionTo('create post', 'edit post');
            }
        }
    }
}
