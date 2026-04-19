<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin - Full system access (handled via Gate::before)
        Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web',
        ]);

        // Admin - Administrative access
        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $admin->givePermissionTo([
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.view',
            'permissions.view',
        ]);

        // Manager - Team management
        $manager = Role::firstOrCreate([
            'name' => 'manager',
            'guard_name' => 'web',
        ]);

        $manager->givePermissionTo([
            'users.view',
            'projects.view',
            'projects.create',
            'projects.update',
            'tasks.view',
            'tasks.create',
            'tasks.update',
        ]);

        // User - Standard access
        $user = Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => 'web',
        ]);

        $user->givePermissionTo([
            'projects.view',
            'tasks.view',
            'tasks.create',
            'tasks.update',
        ]);
    }
}
