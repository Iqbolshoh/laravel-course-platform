<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\{Role, Permission};
use App\Models\User;

/**
 * Class RolePermissionSeeder
 *
 * Sets up RBAC using Spatie Permission package, creating superadmin, teacher, and student roles.
 */
class RolePermissionSeeder extends Seeder
{
    /*
    |---------------------------------------------------------------
    | run
    |---------------------------------------------------------------
    | Seeds roles, permissions, and users for RBAC setup.
    |
    */
    public function run(): void
    {
        /*
        |---------------------------------------------------------------
        | config
        |---------------------------------------------------------------
        | Defines permissions, roles, and users for seeding.
        |
        */
        $config = [
            /*
            |---------------------------------------------------------------
            | permissions
            |---------------------------------------------------------------
            | Defines resources and their allowed actions.
            |
            */
            'permissions' => [
                'user' => ['view', 'create', 'edit', 'delete'],
                'profile' => ['view', 'edit'],
                'session' => ['view', 'delete'],
            ],

            /*
            |---------------------------------------------------------------
            | roles
            |---------------------------------------------------------------
            | Maps superadmin, teacher, and student roles to permissions.
            |
            */
            'roles' => [
                'superadmin' => [], // Gets all permissions
                'teacher' => [
                    'user' => ['view', 'create', 'edit', 'delete'],
                    'profile' => ['view', 'edit'],
                    'session' => ['view', 'delete'],
                ],
                'student' => [
                    'profile' => ['view', 'edit'],
                    'session' => ['view', 'delete'],
                ],
            ],

            /*
            |---------------------------------------------------------------
            | users_by_role
            |---------------------------------------------------------------
            | Groups users by superadmin, teacher, and student roles.
            |
            */
            'users_by_role' => [
                'superadmin' => [
                    ['name' => 'Super Admin', 'email' => 'admin@iqbolshoh.uz', 'password' => bcrypt('IQBOLSHOH')],
                ],
                'teacher' => [
                    ['name' => 'Teacher User', 'email' => 'teacher@iqbolshoh.uz', 'password' => bcrypt('IQBOLSHOH')],
                ],
                'student' => [
                    ['name' => 'Student User', 'email' => 'student@iqbolshoh.uz', 'password' => bcrypt('IQBOLSHOH')],
                ],
            ],
        ];

        /*
        |---------------------------------------------------------------
        | createPermissions
        |---------------------------------------------------------------
        | Creates permissions for defined resources and actions.
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
        | createRolesAndAssignPermissions
        |---------------------------------------------------------------
        | Creates superadmin, teacher, and student roles with permissions.
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
        | createUsersAndAssignRoles
        |---------------------------------------------------------------
        | Creates users and assigns them to superadmin, teacher, or student roles.
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
        | outputSuccessMessage
        |---------------------------------------------------------------
        | Outputs success message after seeding.
        |
        */
        $this->command->info('Superadmin, teacher, and student roles seeded successfully!');
    }
}