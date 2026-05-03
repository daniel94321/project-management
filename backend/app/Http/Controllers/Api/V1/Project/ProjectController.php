<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Project\StoreProjectRequest;
use App\Http\Requests\Api\V1\Project\UpdateProjectRequest;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectService $projectService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters  = $request->only(['search', 'status', 'priority', 'sort_by', 'sort_direction', 'per_page']);
        $projects = $this->projectService->getProjects($filters);

        return ProjectResource::collection($projects);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->projectService->createProject(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'Project created successfully',
            'project' => new ProjectResource($project->load('owner')),
        ], 201);
    }

    public function show(Project $project): JsonResponse
    {
        return response()->json([
            'project' => new ProjectResource($project->load('owner')),
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $project = $this->projectService->updateProject($project, $request->validated());

        return response()->json([
            'message' => 'Project updated successfully',
            'project' => new ProjectResource($project),
        ]);
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->projectService->deleteProject($project);

        return response()->json(['message' => 'Project deleted successfully']);
    }
}
