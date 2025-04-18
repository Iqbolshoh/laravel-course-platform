<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |-------------------------------------------------------------------------- 
        | Define User Data
        |-------------------------------------------------------------------------- 
        | This section defines all necessary users for the system
        */
        $users = [
            ['name' => 'Super Admin', 'email' => 'admin@iqbolshoh.uz', 'password' => Hash::make('IQBOLSHOH')],
            ['name' => 'User', 'email' => 'user@iqbolshoh.uz', 'password' => Hash::make('IQBOLSHOH')]
        ];

        /*
        |-------------------------------------------------------------------------- 
        | Create Users
        |-------------------------------------------------------------------------- 
        | This section creates all necessary users for the system
        */
        collect($users)->each(fn($user) => User::create($user));

        /*
        |-------------------------------------------------------------------------- 
        | Display Success Message
        |-------------------------------------------------------------------------- 
        | This section displays a confirmation message
        */
        $this->command->info('Users seeded: Superadmin & Users!');
    }
}