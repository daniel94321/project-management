<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\PermissionSeeder::class,
            \Database\Seeders\RoleSeeder::class,
        ]);

        $this->authService = $this->app->make(AuthService::class);
    }

    public function test_it_registers_a_user_with_estudiante_role(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $user = $this->authService->register($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertTrue($user->isActive());
        $this->assertTrue($user->hasRole('estudiante'));
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_it_throws_exception_when_credentials_are_incorrect(): void
    {
        $this->expectException(ValidationException::class);

        $this->authService->authenticate([
            'email' => 'nonexistent@example.com',
            'password' => 'wrong-password',
        ]);
    }

    public function test_it_throws_exception_when_account_is_inactive(): void
    {
        $user = User::factory()->inactive()->create([
            'email' => 'inactive@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Your account is not active');

        $this->authService->authenticate([
            'email' => 'inactive@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_it_authenticates_active_user(): void
    {
        $user = User::factory()->create([
            'email' => 'active@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        $authenticated = $this->authService->authenticate([
            'email' => 'active@example.com',
            'password' => 'correct-password',
        ]);

        $this->assertInstanceOf(User::class, $authenticated);
        $this->assertEquals($user->id, $authenticated->id);
    }

    public function test_it_updates_last_login(): void
    {
        $user = User::factory()->create(['last_login_at' => null]);

        $this->authService->updateLastLogin($user);

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_it_returns_null_when_no_user_is_authenticated(): void
    {
        $currentUser = $this->authService->getCurrentUser();

        $this->assertNull($currentUser);
    }
}
