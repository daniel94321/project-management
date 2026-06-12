<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Project;

use App\Models\Project;
use App\Models\ProjectCommunication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectCommunicationControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $coordinator;
    private User $student;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\PermissionSeeder::class,
            \Database\Seeders\RoleSeeder::class,
        ]);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrador');

        $this->coordinator = User::factory()->create();
        $this->coordinator->assignRole('coordinador');

        $this->student = User::factory()->create();
        $this->student->assignRole('estudiante');

        $this->project = Project::factory()->create(['owner_id' => $this->student->id]);
    }

    public function test_student_can_send_communication(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson("/api/v1/projects/{$this->project->id}/communications", [
                'request_type' => 'modify_project',
                'message' => 'I need to modify the scope',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Communication sent successfully');
    }

    public function test_student_cannot_send_duplicate_pending_communication(): void
    {
        ProjectCommunication::create([
            'project_id' => $this->project->id,
            'user_id' => $this->student->id,
            'request_type' => 'modify_project',
            'message' => 'First request',
            'status' => ProjectCommunication::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->student)
            ->postJson("/api/v1/projects/{$this->project->id}/communications", [
                'request_type' => 'postpone_project',
                'message' => 'Second request',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Ya tienes una solicitud pendiente para este proyecto.');
    }

    public function test_admin_can_view_communications(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/project-communications');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_coordinator_can_view_communications(): void
    {
        $response = $this->actingAs($this->coordinator)
            ->getJson('/api/v1/project-communications');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_student_cannot_view_all_communications(): void
    {
        $response = $this->actingAs($this->student)
            ->getJson('/api/v1/project-communications');

        $response->assertStatus(403);
    }

    public function test_admin_can_resolve_communication(): void
    {
        $communication = ProjectCommunication::create([
            'project_id' => $this->project->id,
            'user_id' => $this->student->id,
            'request_type' => 'modify_project',
            'message' => 'Help needed',
            'status' => ProjectCommunication::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/v1/project-communications/{$communication->id}", [
                'status' => ProjectCommunication::STATUS_APPROVED,
                'response' => 'Changes approved!',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Communication updated successfully');

        $this->assertDatabaseHas('project_communications', [
            'id' => $communication->id,
            'status' => ProjectCommunication::STATUS_APPROVED,
        ]);
    }

    public function test_communication_validates_required_fields(): void
    {
        $response = $this->actingAs($this->student)
            ->postJson("/api/v1/projects/{$this->project->id}/communications", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['request_type', 'message']);
    }

    public function test_resolve_validates_status_field(): void
    {
        $communication = ProjectCommunication::create([
            'project_id' => $this->project->id,
            'user_id' => $this->student->id,
            'request_type' => 'modify_project',
            'message' => 'Help needed',
            'status' => ProjectCommunication::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/v1/project-communications/{$communication->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_unauthenticated_user_cannot_access_communications(): void
    {
        $response = $this->getJson('/api/v1/project-communications');

        $response->assertStatus(401);
    }
}
