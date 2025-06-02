<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Class DatabaseSeeder
 *
 * This seeder serves as the entry point for seeding the application's database.
 * It orchestrates the execution of other seeders to populate initial data.
 */
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
        | Execute RolePermissionSeeder
        |-------------------------------------------------------------------------- 
        | Calls the RolePermissionSeeder to create roles, permissions, and assign them to users.
        */
        $this->call(RolePermissionSeeder::class);
    }
}