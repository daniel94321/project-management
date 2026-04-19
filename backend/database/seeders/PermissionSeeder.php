<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User Management
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            // Role Management
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',

            // Permission Management
            'permissions.view',
            'permissions.assign',

            // Future: Project Management
            'projects.view',
            'projects.create',
            'projects.update',
            'projects.delete',

            // Future: Task Management
            'tasks.view',
            'tasks.create',
            'tasks.update',
            'tasks.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
