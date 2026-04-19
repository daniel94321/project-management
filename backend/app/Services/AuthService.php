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

        $user = Auth::user();

        if (!$user->isActive()) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['Your account is not active. Please contact support.'],
            ]);
        }

        return $user;
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
        $user = Auth::user();

        if ($user) {
            $user->load('roles.permissions');
        }

        return $user;
    }
}
