<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\{Role, Permission};
use App\Models\User;

/**
 * Class RolePermissionSeeder
 *
 * This seeder sets up role-based access control (RBAC) using the Spatie Permission package.
 * It creates permissions, roles, and users, and assigns roles to users based on a configuration array.
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeding process.
     *
     * Initializes permissions, creates roles with their associated permissions, and seeds users
     * with predefined roles. Outputs a success message upon completion.
     *
     * @return void
     */
    public function run(): void
    {
        /*
        |---------------------------------------------------------------
        | Configuration Array
        |---------------------------------------------------------------
        |
        | Defines the structure for permissions, roles, and users.
        | - 'permissions': Lists resources and their allowed actions (e.g., view, create).
        | - 'roles': Maps roles to their specific permissions (superadmin gets all).
        | - 'users_by_role': Groups users by role with their credentials.
        |
        */
        $config = [
            /*
            |---------------------------------------------------------------
            | Resource Permissions
            |---------------------------------------------------------------
            |
            | Defines available permissions for each resource in the format:
            | 'resource' => ['action1', 'action2', ...].
            | Example: 'user' => ['view', 'create'] creates 'user.view', 'user.create'.
            |
            */
            'permissions' => [
                'user' => ['view', 'create', 'edit', 'delete'],
                'profile' => ['view', 'edit'],
                'session' => ['view', 'delete'],
            ],
            
            /*
            |---------------------------------------------------------------
            | Roles and Their Permissions
            |---------------------------------------------------------------
            |
            | Assigns permissions to roles. The 'superadmin' role receives all permissions
            | automatically, while other roles (manager, user) have specific permissions.
            |
            */
            'roles' => [
                'superadmin' => [], // Automatically assigned all permissions
                'manager' => [
                    'user' => ['view', 'create', 'edit', 'delete'],
                    'profile' => ['view', 'edit'],
                    'session' => ['view', 'delete'],
                ],
                'user' => [
                    'profile' => ['view', 'edit'],
                    'session' => ['view', 'delete'],
                ],
            ],

            /*
            |---------------------------------------------------------------
            | Users Grouped by Role
            |---------------------------------------------------------------
            |
            | Defines users for each role with their name, email, and password.
            | Used for seeding initial users for testing or demo purposes.
            |
            */
            'users_by_role' => [
                'superadmin' => [
                    ['name' => 'Super Admin', 'email' => 'superadmin@iqbolshoh.uz', 'password' => bcrypt('IQBOLSHOH')],
                ],
                'manager' => [
                    ['name' => 'Manager User', 'email' => 'manager@iqbolshoh.uz', 'password' => bcrypt('IQBOLSHOH')],
                ],
                'user' => [
                    ['name' => 'Regular User', 'email' => 'user@iqbolshoh.uz', 'password' => bcrypt('IQBOLSHOH')],
                ],
            ],
        ];

        /*
        |---------------------------------------------------------------
        | Create Permissions
        |---------------------------------------------------------------
        |
        | Iterates through the permissions configuration to create permissions
        | in the format "resource.action" (e.g., "user.view", "profile.edit").
        | Uses firstOrCreate to avoid duplicates and sets the 'web' guard.
        |
        */
        collect($config['permissions'])->each(
            fn($actions, $resource) =>
            collect($actions)->each(
                fn($action) =>
                Permission::firstOrCreate(['name' => "$resource.$action", 'guard_name' => 'web'])
            )
        );

        /*
        |---------------------------------------------------------------
        | Create Roles and Assign Permissions
        |---------------------------------------------------------------
        |
        | Creates roles and assigns permissions based on the roles configuration.
        | - Superadmin gets all permissions dynamically.
        | - Other roles (manager, user) get only their specified permissions.
        | Uses syncPermissions to ensure roles have exactly the listed permissions.
        |
        */
        collect($config['roles'])->each(function ($permissions, $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $perms = $roleName === 'superadmin' ? Permission::pluck('name') : collect($permissions)
                ->flatMap(fn($actions, $resource) => collect($actions)->map(fn($action) => "$resource.$action"));
            $role->syncPermissions($perms);
        });

        /*
        |---------------------------------------------------------------
        | Create Users and Assign Roles
        |---------------------------------------------------------------
        |
        | Creates users from the users_by_role configuration and assigns their
        | respective roles. Uses firstOrCreate to prevent duplicate users and
        | syncRoles to ensure each user has exactly one role.
        |
        */
        collect($config['users_by_role'])->each(
            fn($users, $roleName) =>
            collect($users)->each(fn($userData) => User::firstOrCreate(
                ['email' => $userData['email']],
                ['name' => $userData['name'], 'password' => $userData['password']]
            )->syncRoles($roleName))
        );

        /*
        |---------------------------------------------------------------
        | Success Confirmation
        |---------------------------------------------------------------
        |
        | Outputs a confirmation message to the console to indicate that
        | permissions, roles, and users have been successfully seeded.
        |
        */
        $this->command->info('Permissions, roles, and users seeded successfully!');
    }
}