<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'email' => 'admin@example.com',
                'name' => 'Administrador Demo',
                'role' => 'administrador',
            ],
            [
                'email' => 'manager@example.com',
                'name' => 'Coordinador Demo',
                'role' => 'coordinador',
            ],
            [
                'email' => 'user@example.com',
                'name' => 'Estudiante Demo',
                'role' => 'estudiante',
            ],
            [
                'email' => 'evaluador@example.com',
                'name' => 'Evaluador Demo',
                'role' => 'evaluador',
            ],
            [
                'email' => 'director@example.com',
                'name' => 'Director Demo',
                'role' => 'director',
            ],
        ];

        foreach ($users as $demoUser) {
            $user = User::updateOrCreate(
                ['email' => $demoUser['email']],
                [
                    'name' => $demoUser['name'],
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$demoUser['role']]);
        }
    }
}
