<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_is_active_when_status_is_active(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $this->assertTrue($user->isActive());
    }

    public function test_user_is_not_active_when_status_is_inactive(): void
    {
        $user = User::factory()->inactive()->create();

        $this->assertFalse($user->isActive());
    }

    public function test_user_is_not_active_when_status_is_suspended(): void
    {
        $user = User::factory()->suspended()->create();

        $this->assertFalse($user->isActive());
    }

    public function test_user_is_super_admin_when_has_super_admin_role(): void
    {
        $this->seed([
            \Database\Seeders\PermissionSeeder::class,
            \Database\Seeders\RoleSeeder::class,
        ]);

        \Spatie\Permission\Models\Role::create(['name' => 'super-admin', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->assertTrue($user->isSuperAdmin());
    }

    public function test_regular_user_is_not_super_admin(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_user_uses_uuid_as_primary_key(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->id);
        $this->assertTrue(strlen($user->id) === 36);
    }

    public function test_user_can_be_soft_deleted(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $user->delete();

        $this->assertSoftDeleted($user);
        $this->assertNull(User::find($userId));
        $this->assertNotNull(User::withTrashed()->find($userId));
    }

    public function test_password_is_always_hashed(): void
    {
        $user = User::factory()->create([
            'password' => 'plain-text-pass',
        ]);

        $this->assertNotEquals('plain-text-pass', $user->password);
        $this->assertTrue(password_verify('plain-text-pass', $user->password));
    }

    public function test_hidden_attributes_are_not_serialized(): void
    {
        $user = User::factory()->create();
        $serialized = $user->toArray();

        $this->assertArrayNotHasKey('password', $serialized);
        $this->assertArrayNotHasKey('remember_token', $serialized);
    }
}
