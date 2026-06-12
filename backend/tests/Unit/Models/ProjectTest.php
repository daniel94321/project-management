<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_belongs_to_owner(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $this->assertInstanceOf(User::class, $project->owner);
        $this->assertEquals($owner->id, $project->owner->id);
    }

    public function test_project_uses_uuid_as_primary_key(): void
    {
        $project = Project::factory()->create();

        $this->assertNotNull($project->id);
        $this->assertTrue(strlen($project->id) === 36);
    }

    public function test_project_can_be_soft_deleted(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $projectId = $project->id;

        $project->delete();

        $this->assertSoftDeleted($project);
        $this->assertNull(Project::find($projectId));
    }

    public function test_project_casts_dates_correctly(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create([
            'owner_id' => $owner->id,
            'start_date' => '2026-01-15',
            'end_date' => '2026-07-14',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $project->start_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $project->end_date);
    }

    public function test_project_fillable_attributes(): void
    {
        $owner = User::factory()->create();

        $project = Project::create([
            'name' => 'Fillable Test',
            'description' => 'Testing fillable attributes',
            'status' => 'planning',
            'priority' => 'high',
            'start_date' => '2026-06-01',
            'end_date' => '2026-11-28',
            'owner_id' => $owner->id,
        ]);

        $this->assertEquals('Fillable Test', $project->name);
        $this->assertEquals('planning', $project->status);
        $this->assertEquals('high', $project->priority);
    }

    public function test_project_has_valid_statuses(): void
    {
        $owner = User::factory()->create();

        $planning = Project::factory()->create(['owner_id' => $owner->id, 'status' => 'planning']);
        $active = Project::factory()->create(['owner_id' => $owner->id, 'status' => 'active']);
        $completed = Project::factory()->create(['owner_id' => $owner->id, 'status' => 'completed']);
        $cancelled = Project::factory()->create(['owner_id' => $owner->id, 'status' => 'cancelled']);

        $this->assertEquals('planning', $planning->status);
        $this->assertEquals('active', $active->status);
        $this->assertEquals('completed', $completed->status);
        $this->assertEquals('cancelled', $cancelled->status);
    }
}
