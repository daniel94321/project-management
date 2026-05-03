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
        $roles = [
            'administrador' => [
                'users.view',
                'users.create',
                'users.update',
                'users.delete',
                'roles.view',
                'roles.create',
                'roles.update',
                'roles.delete',
                'permissions.view',
                'permissions.assign',
                'projects.view',
                'projects.create',
                'projects.update',
                'projects.delete',
                'tasks.view',
                'tasks.create',
                'tasks.update',
                'tasks.delete',
            ],
            'coordinador' => [
                'users.view',
                'projects.view',
                'projects.create',
                'projects.update',
                'tasks.view',
                'tasks.create',
                'tasks.update',
            ],
            'evaluador' => [
                'projects.view',
                'tasks.view',
                'tasks.update',
            ],
            'director' => [
                'users.view',
                'projects.view',
                'tasks.view',
                'roles.view',
                'permissions.view',
            ],
            'estudiante' => [
                'projects.view',
                'projects.create',
                'tasks.view',
                'tasks.create',
                'tasks.update',
            ],
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($permissions);
        }
    }
}
