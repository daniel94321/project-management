<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Attempt to authenticate user with given credentials.
     *
     * @param array<string, mixed> $credentials
     * @throws ValidationException
     */
    public function authenticate(array $credentials, bool $remember = false): User
    {
        if (!Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        /** @var User|null $user */
        $user = Auth::user();

        if (!$user instanceof User) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->isActive()) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['Your account is not active. Please contact support.'],
            ]);
        }

        return $user;
    }

    /**
     * Register a public user with the estudiante role.
     *
     * @param array<string, mixed> $data
     */
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'status' => 'active',
        ]);

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        $user->syncRoles(['estudiante']);

        return $user->load('roles.permissions');
    }

    /**
     * Update user's last login timestamp.
     */
    public function updateLastLogin(User $user): void
    {
        $user->update(['last_login_at' => now()]);
    }

    /**
     * Logout the current user.
     */
    public function logout(): void
    {
        Auth::guard('web')->logout();
    }

    /**
     * Get the currently authenticated user with relationships.
     */
    public function getCurrentUser(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user instanceof User) {
            $user->load('roles.permissions');
        }

        return $user;
    }
}
