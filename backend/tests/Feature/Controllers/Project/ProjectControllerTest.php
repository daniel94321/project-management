<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\PermissionSeeder::class,
            \Database\Seeders\RoleSeeder::class,
        ]);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrador');

        $this->student = User::factory()->create();
        $this->student->assignRole('estudiante');
    }

    public function test_user_can_list_projects(): void
    {
        Project::factory()->count(3)->create(['owner_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/projects');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_user_without_permission_cannot_list_projects(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/v1/projects');

        $response->assertStatus(403);
    }

    public function test_student_can_create_project(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson('/api/v1/projects', [
                'name' => 'Student Project',
                'description' => 'Created by a student',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Project created successfully');

        $this->assertDatabaseHas('projects', [
            'name' => 'Student Project',
            'owner_id' => $this->student->id,
        ]);
    }

    public function test_student_project_gets_planning_status_and_medium_priority(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson('/api/v1/projects', [
                'name' => 'Auto Defaults',
            ]);

        $response->assertStatus(201);

        $project = Project::where('name', 'Auto Defaults')->first();
        $this->assertEquals('planning', $project->status);
        $this->assertEquals('medium', $project->priority);
    }

    public function test_create_project_validates_required_fields(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson('/api/v1/projects', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_user_can_view_project(): void
    {
        $project = Project::factory()->create(['owner_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/projects/{$project->id}");

        $response->assertStatus(200)
            ->assertJsonPath('project.id', $project->id);
    }

    public function test_user_can_update_project(): void
    {
        $project = Project::factory()->create([
            'owner_id' => $this->admin->id,
            'name' => 'Original Name',
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/projects/{$project->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Project updated successfully');

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_user_can_delete_project(): void
    {
        $project = Project::factory()->create(['owner_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/projects/{$project->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Project deleted successfully');

        $this->assertSoftDeleted($project);
    }

    public function test_unauthenticated_user_cannot_access_projects(): void
    {
        $response = $this->getJson('/api/v1/projects');

        $response->assertStatus(401);
    }

    public function test_user_without_permission_cannot_delete_project(): void
    {
        $project = Project::factory()->create(['owner_id' => $this->admin->id]);

        $response = $this->actingAs($this->student)
            ->deleteJson("/api/v1/projects/{$project->id}");

        $response->assertStatus(403);
    }
}
