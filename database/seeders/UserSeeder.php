<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $user = User::find(1);
        if ($user) return;

        $user = new User();
        $user->id = 1;
        $user->name = 'Artem';
        $user->phone = '380958056988';
        $user->email = '380958056988@gmail.com';
        $user->setPassword('password');
        $user->save();
        $user->assignRole('admin');

//        $user = User::factory()->create();

//        $user->givePermissionTo(Permission::all());
//        $user = User::find(1); // Или любой другой способ поиска пользователя
//        $user->assignRole('admin');

    }
}
