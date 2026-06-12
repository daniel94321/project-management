<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\PermissionSeeder::class,
            \Database\Seeders\RoleSeeder::class,
        ]);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrador');

        $this->regularUser = User::factory()->create();
        $this->regularUser->assignRole('estudiante');
    }

    public function test_admin_can_list_users(): void
    {
        User::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/users');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_user_without_permission_cannot_list_users(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->getJson('/api/v1/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_user(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/users', [
                'name' => 'New Created User',
                'email' => 'created@example.com',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'User created successfully');

        $this->assertDatabaseHas('users', [
            'email' => 'created@example.com',
            'name' => 'New Created User',
        ]);
    }

    public function test_create_user_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/users', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_admin_can_view_user(): void
    {
        $target = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/users/{$target->id}");

        $response->assertStatus(200)
            ->assertJsonPath('user.id', $target->id);
    }

    public function test_admin_can_update_user(): void
    {
        $target = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/users/{$target->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'User updated successfully');

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_admin_can_delete_user(): void
    {
        $target = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/users/{$target->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'User deleted successfully');

        $this->assertSoftDeleted($target);
    }

    public function test_user_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/users/{$this->admin->id}");

        $response->assertStatus(422);
    }

    public function test_unauthenticated_user_cannot_access_users(): void
    {
        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(401);
    }

    public function test_user_without_permission_cannot_create_user(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->postJson('/api/v1/users', [
                'name' => 'Unauthorized',
                'email' => 'unauth@example.com',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
            ]);

        $response->assertStatus(403);
    }
}
