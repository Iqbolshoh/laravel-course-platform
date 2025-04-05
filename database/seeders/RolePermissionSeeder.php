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
                'role' => ['view', 'create', 'edit', 'delete'],
                'user' => ['view', 'create', 'edit', 'delete'],
                'profile' => ['view', 'edit', 'delete'],
                'course' => ['create', 'view', 'edit', 'delete'],
                'lesson' => ['create', 'view', 'edit', 'delete'],
                'exam' => ['create', 'view', 'edit', 'delete'],
                'certificate' => ['create', 'view', 'edit', 'delete'],
                'payment' => ['view', 'delete'],
            ],
            'roles' => [
                'superadmin',
                'teacher',
                'student'
            ],
            'role_permissions' => [
                'teacher' => [
                    'profile' => ['view', 'edit'],
                    'course' => ['create', 'view', 'edit', 'delete'],
                    'lesson' => ['create', 'view', 'edit', 'delete'],
                    'exam' => ['create', 'view', 'edit', 'delete'],
                    'certificate' => ['view'],
                    'payment' => ['view'],
                ],
                'student' => [
                    'profile' => ['view', 'edit'],
                    'course' => ['view'],
                    'lesson' => ['view'],
                    'exam' => ['view'],
                    'certificate' => ['view'],
                    'payment' => ['view'],
                ]
            ],
            'user_roles' => [
                'admin@iqbolshoh.uz' => 'superadmin',
                'teacher@iqbolshoh.uz' => 'teacher',
                'student@iqbolshoh.uz' => 'student',
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
