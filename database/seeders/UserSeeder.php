<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создание одного пользователя
        $user = User::factory()->create();

        $user->givePermissionTo(Permission::all());
//        $user = User::find(1); // Или любой другой способ поиска пользователя
//        $user->assignRole('admin');

    }
}
