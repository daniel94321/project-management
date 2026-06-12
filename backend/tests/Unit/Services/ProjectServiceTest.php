<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProjectService $projectService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            \Database\Seeders\PermissionSeeder::class,
            \Database\Seeders\RoleSeeder::class,
        ]);

        $this->projectService = $this->app->make(ProjectService::class);
    }

    public function test_it_creates_project_with_student_defaults(): void
    {
        $student = User::factory()->create();
        $student->assignRole('estudiante');

        $project = $this->projectService->createProject([
            'name' => 'Test Project',
            'description' => 'A test project',
        ], $student);

        $this->assertInstanceOf(Project::class, $project);
        $this->assertEquals('Test Project', $project->name);
        $this->assertEquals('planning', $project->status);
        $this->assertEquals('medium', $project->priority);
        $this->assertEquals($student->id, $project->owner_id);
    }

    public function test_it_creates_project_for_non_student_with_custom_values(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('administrador');

        $project = $this->projectService->createProject([
            'name' => 'Admin Project',
            'status' => 'active',
            'priority' => 'high',
        ], $admin);

        $this->assertEquals('active', $project->status);
        $this->assertEquals('high', $project->priority);
    }

    public function test_it_calculates_end_date_from_start_date(): void
    {
        $student = User::factory()->create();
        $student->assignRole('estudiante');

        $project = $this->projectService->createProject([
            'name' => 'Dated Project',
            'start_date' => '2026-01-15',
        ], $student);

        $this->assertEquals('2026-07-14', $project->end_date->toDateString());
    }

    public function test_it_returns_null_end_date_when_no_start_date(): void
    {
        $student = User::factory()->create();
        $student->assignRole('estudiante');

        $project = $this->projectService->createProject([
            'name' => 'No Date Project',
        ], $student);

        $this->assertNull($project->end_date);
    }

    public function test_it_updates_end_date_when_start_date_changes(): void
    {
        $student = User::factory()->create();
        $student->assignRole('estudiante');

        $project = $this->projectService->createProject([
            'name' => 'Update Test',
            'start_date' => '2026-01-01',
        ], $student);

        $updated = $this->projectService->updateProject($project, [
            'start_date' => '2026-03-01',
        ]);

        $this->assertEquals('2026-08-28', $updated->end_date->toDateString());
    }

    public function test_it_does_not_change_end_date_when_start_date_not_provided(): void
    {
        $student = User::factory()->create();
        $student->assignRole('estudiante');

        $project = $this->projectService->createProject([
            'name' => 'Stable Date',
            'start_date' => '2026-01-01',
        ], $student);

        $originalEndDate = $project->end_date;

        $updated = $this->projectService->updateProject($project, [
            'name' => 'Renamed',
        ]);

        $this->assertEquals($originalEndDate->toDateString(), $updated->end_date->toDateString());
    }

    public function test_it_deletes_project_softly(): void
    {
        $student = User::factory()->create();
        $student->assignRole('estudiante');

        $project = $this->projectService->createProject([
            'name' => 'To Delete',
        ], $student);

        $this->projectService->deleteProject($project);

        $this->assertSoftDeleted($project);
    }

    public function test_it_filters_projects_by_status(): void
    {
        $owner = User::factory()->create();
        Project::factory()->create(['status' => 'active', 'owner_id' => $owner->id]);
        Project::factory()->create(['status' => 'planning', 'owner_id' => $owner->id]);

        $result = $this->projectService->getProjects(['status' => 'active']);

        $this->assertEquals(1, $result->total());
    }

    public function test_it_searches_projects_by_name(): void
    {
        $owner = User::factory()->create();
        Project::factory()->create(['name' => 'Alpha Project', 'owner_id' => $owner->id]);
        Project::factory()->create(['name' => 'Beta Project', 'owner_id' => $owner->id]);

        $result = $this->projectService->getProjects(['search' => 'Alpha']);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Alpha Project', $result->first()->name);
    }
}
