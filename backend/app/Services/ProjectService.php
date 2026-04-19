<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Project;
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

    public function createProject(array $data, string $ownerId): Project
    {
        return Project::create([
            ...$data,
            'owner_id' => $ownerId,
        ]);
    }

    public function updateProject(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->fresh('owner');
    }

    public function deleteProject(Project $project): void
    {
        $project->delete();
    }
}
