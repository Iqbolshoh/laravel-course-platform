<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\{Role, Permission};
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Define Permissions and Roles
        |--------------------------------------------------------------------------
        | This section sets up all the necessary permissions and roles for the system
        */
        $config = [
            'permissions' => [
                'profile' => ['view', 'edit', 'delete']
            ],
            'roles' => [
                'superadmin',
                'user'
                // Add a new role here
            ],
            'role_permissions' => [
                'user' => [
                    'profile' => ['view', 'edit']
                ],
                // Add permissions for a new role here
            ],
            'user_roles' => [
                'admin@iqbolshoh.uz' => 'superadmin',
                'user@iqbolshoh.uz' => 'user',
                // Add a new user here
            ]
        ];

        /*
        |--------------------------------------------------------------------------
        | Create permissions
        |--------------------------------------------------------------------------
        | This section creates all necessary permissions for the system
        */
        collect($config['permissions'])->each(
            fn($perms, $group) =>
            collect($perms)->each(fn($perm) => Permission::firstOrCreate(['name' => "$group.$perm", 'guard_name' => 'web']))
        );

        /*
        |--------------------------------------------------------------------------
        | Create roles and assign permissions
        |--------------------------------------------------------------------------
        | This section creates roles and assigns the defined permissions
        */
        collect($config['roles'])->each(
            fn($role) =>
            tap(
                Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']),
                fn($roleInstance) => $roleInstance->syncPermissions(
                    collect($config['role_permissions'][$role] ?? [])->flatMap(fn($perms, $group) => collect($perms)->map(fn($perm) => "$group.$perm"))
                )
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Assign roles to users
        |--------------------------------------------------------------------------
        | This section assigns predefined roles to specific users
        */
        collect($config['user_roles'])->each(
            fn($role, $email) =>
            User::where('email', $email)->first()?->assignRole($role)
        );

        /*
        |--------------------------------------------------------------------------
        | Display success message
        |--------------------------------------------------------------------------
        | Output a message confirming successful creation of roles and permissions
        */
        $this->command->info('Roles and Permissions created successfully!');
    }
}
