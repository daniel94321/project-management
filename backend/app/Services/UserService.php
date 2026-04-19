<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    /**
     * Get paginated list of users with filters.
     *
     * @param array<string, mixed> $filters
     */
    public function getUsers(array $filters = []): LengthAwarePaginator
    {
        $query = User::with('roles');

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Role filter
        if (!empty($filters['role'])) {
            $query->whereHas('roles', fn($q) => $q->where('name', $filters['role']));
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        $allowedSortFields = ['created_at', 'name', 'email', 'status'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    /**
     * Create a new user.
     *
     * @param array<string, mixed> $data
     */
    public function createUser(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => $data['status'] ?? 'active',
        ]);

        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return $user->load('roles');
    }

    /**
     * Update an existing user.
     *
     * @param array<string, mixed> $data
     * @throws ValidationException
     */
    public function updateUser(User $user, array $data): User
    {
        $updateData = array_filter([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'status' => $data['status'] ?? null,
        ], fn($value) => $value !== null);

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        if (isset($data['roles'])) {
            $this->syncUserRoles($user, $data['roles']);
        }

        return $user->load('roles');
    }

    /**
     * Sync user roles with validation.
     *
     * @param array<string> $roles
     * @throws ValidationException
     */
    public function syncUserRoles(User $user, array $roles): void
    {
        // Prevent removing super-admin role from last super-admin
        if ($user->hasRole('super-admin') && !in_array('super-admin', $roles)) {
            $superAdminCount = User::role('super-admin')->count();
            if ($superAdminCount <= 1) {
                throw ValidationException::withMessages([
                    'roles' => ['Cannot remove super-admin role from the last super-admin user.'],
                ]);
            }
        }

        $user->syncRoles($roles);
    }

    /**
     * Delete a user with validation.
     *
     * @throws ValidationException
     */
    public function deleteUser(User $user, string $currentUserId): void
    {
        // Prevent self-deletion
        if ($user->id === $currentUserId) {
            throw ValidationException::withMessages([
                'user' => ['You cannot delete your own account.'],
            ]);
        }

        // Prevent deletion of last super-admin
        if ($user->hasRole('super-admin')) {
            $superAdminCount = User::role('super-admin')->count();
            if ($superAdminCount <= 1) {
                throw ValidationException::withMessages([
                    'user' => ['Cannot delete the last super-admin user.'],
                ]);
            }
        }

        $user->delete();
    }

    /**
     * Find user by ID with roles.
     */
    public function findById(string $id): ?User
    {
        return User::with('roles.permissions')->find($id);
    }
}
