<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Role;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_roles(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/v1/roles');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_unauthenticated_user_cannot_list_roles(): void
    {
        $response = $this->getJson('/api/v1/roles');

        $response->assertStatus(401);
    }
}
