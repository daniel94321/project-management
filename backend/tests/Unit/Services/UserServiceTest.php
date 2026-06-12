<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\PermissionSeeder::class,
            \Database\Seeders\RoleSeeder::class,
        ]);

        $this->userService = $this->app->make(UserService::class);
    }

    public function test_it_creates_a_user(): void
    {
        $user = $this->userService->createUser([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secure-password',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('active', $user->status);
    }

    public function test_it_creates_user_with_roles(): void
    {
        $user = $this->userService->createUser([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'secure-password',
            'roles' => ['administrador'],
        ]);

        $this->assertTrue($user->hasRole('administrador'));
    }

    public function test_it_creates_user_with_custom_status(): void
    {
        $user = $this->userService->createUser([
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'password' => 'secure-password',
            'status' => 'inactive',
        ]);

        $this->assertEquals('inactive', $user->status);
    }

    public function test_it_updates_user(): void
    {
        $user = User::factory()->create();

        $updated = $this->userService->updateUser($user, [
            'name' => 'Updated Name',
        ]);

        $this->assertEquals('Updated Name', $updated->name);
    }

    public function test_it_prevents_removing_last_admin_role(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrador');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No se puede quitar el rol de administrador');

        $this->userService->syncUserRoles($admin, ['estudiante']);
    }

    public function test_it_allows_removing_admin_role_when_other_admins_exist(): void
    {
        $admin1 = User::factory()->create();
        $admin1->assignRole('administrador');

        $admin2 = User::factory()->create();
        $admin2->assignRole('administrador');

        $this->userService->syncUserRoles($admin1, ['estudiante']);

        $this->assertFalse($admin1->fresh()->hasRole('administrador'));
        $this->assertTrue($admin1->fresh()->hasRole('estudiante'));
    }

    public function test_it_prevents_deleting_own_account(): void
    {
        $user = User::factory()->create();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('You cannot delete your own account');

        $this->userService->deleteUser($user, $user->id);
    }

    public function test_it_prevents_deleting_last_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrador');

        $currentUser = User::factory()->create();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No se puede eliminar al ultimo usuario administrador');

        $this->userService->deleteUser($admin, $currentUser->id);
    }

    public function test_it_deletes_user(): void
    {
        $user = User::factory()->create();
        $currentUser = User::factory()->create();

        $this->userService->deleteUser($user, $currentUser->id);

        $this->assertSoftDeleted($user);
    }

    public function test_it_filters_users_by_status(): void
    {
        User::factory()->count(3)->create(['status' => 'active']);
        User::factory()->count(2)->inactive()->create();

        $result = $this->userService->getUsers(['status' => 'active']);

        $this->assertEquals(3, $result->total());
    }

    public function test_it_searches_users_by_name(): void
    {
        User::factory()->create(['name' => 'John Smith']);
        User::factory()->create(['name' => 'Jane Doe']);

        $result = $this->userService->getUsers(['search' => 'John']);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('John Smith', $result->first()->name);
    }
}
