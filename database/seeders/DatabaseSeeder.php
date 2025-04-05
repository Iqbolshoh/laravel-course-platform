<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with initial data.
     *
     * @return void
     */
    public function run()
    {
        /*
        |-------------------------------------------------------------------------- 
        | Call UserSeeder
        |-------------------------------------------------------------------------- 
        | This section triggers the UserSeeder to create initial users in the system
        */
        $this->call(UserSeeder::class);

        /*
        |-------------------------------------------------------------------------- 
        | Call RolePermissionSeeder
        |-------------------------------------------------------------------------- 
        | This section triggers the RolePermissionSeeder to set up roles and permissions
        */
        $this->call(RolePermissionSeeder::class);
    }
}