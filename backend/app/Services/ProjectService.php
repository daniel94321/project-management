<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectService
{
    public function getProjects(array $filters = []): LengthAwarePaginator
    {
        $query = Project::with('owner');

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        $sortBy        = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        $perPage = (int) ($filters['per_page'] ?? 10);

        return $query->paginate($perPage);
    }

    public function createProject(array $data, User $owner): Project
    {
        $isStudent = $owner->hasRole('estudiante');
        $startDate = $data['start_date'] ?? null;

        return Project::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'status'      => $isStudent ? 'planning' : ($data['status'] ?? 'planning'),
            'priority'    => $isStudent ? 'medium' : ($data['priority'] ?? 'medium'),
            'start_date'  => $startDate,
            'end_date'    => $this->calculateEndDate($startDate),
            'owner_id'    => $owner->id,
        ]);
    }

    public function updateProject(Project $project, array $data): Project
    {
        if (array_key_exists('start_date', $data)) {
            $data['end_date'] = $this->calculateEndDate($data['start_date']);
        }

        $project->update($data);

        return $project->fresh('owner');
    }

    public function deleteProject(Project $project): void
    {
        $project->delete();
    }

    private function calculateEndDate(?string $startDate): ?string
    {
        if (! $startDate) {
            return null;
        }

        try {
            return Carbon::parse($startDate)->addDays(180)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
