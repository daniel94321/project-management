<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Project\StoreProjectCommunicationRequest;
use App\Http\Requests\Api\V1\Project\ResolveProjectCommunicationRequest;
use App\Http\Resources\Api\V1\ProjectCommunicationResource;
use App\Models\Project;
use App\Models\ProjectCommunication;
use App\Models\User;
use App\Notifications\ProjectCommunicationNotification;
use App\Notifications\ProjectCommunicationResolvedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Notification;

class ProjectCommunicationController extends Controller
{
    public function show(Request $request, ProjectCommunication $communication): JsonResponse
    {
        $user = $request->user();

        abort_unless($user && (
            $user->hasRole('administrador') ||
            $user->hasRole('coordinador') ||
            $user->hasRole('director') ||
            $user->hasRole('evaluador')
        ), 403);

        return response()->json([
            'communication' => new ProjectCommunicationResource($communication->load(['project.owner', 'user', 'resolvedBy'])),
        ]);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        abort_unless($user && ($user->hasRole('administrador') || $user->hasRole('coordinador')), 403);

        $communications = ProjectCommunication::with(['project.owner', 'user', 'resolvedBy'])
            ->latest()
            ->paginate(10);

        return ProjectCommunicationResource::collection($communications);
    }

    public function resolve(ResolveProjectCommunicationRequest $request, ProjectCommunication $communication): JsonResponse
    {
        $validated = $request->validated();

        $communication->update([
            'status' => $validated['status'],
            'response' => $validated['response'] ?? null,
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        Notification::send($communication->user, new ProjectCommunicationResolvedNotification(
            $communication->load('project'),
            $request->user(),
        ));

        return response()->json([
            'message' => 'Communication updated successfully',
            'communication' => new ProjectCommunicationResource($communication->fresh(['project.owner', 'user', 'resolvedBy'])),
        ]);
    }

    public function store(StoreProjectCommunicationRequest $request, Project $project): JsonResponse
    {
        $hasPendingCommunication = ProjectCommunication::query()
            ->where('project_id', $project->id)
            ->where('user_id', $request->user()->id)
            ->where('status', ProjectCommunication::STATUS_PENDING)
            ->exists();

        if ($hasPendingCommunication) {
            return response()->json([
                'message' => 'Ya tienes una solicitud pendiente para este proyecto.',
                'errors' => [
                    'request_type' => ['Solo puedes tener una solicitud pendiente por proyecto hasta que sea resuelta.'],
                ],
            ], 422);
        }

        $communication = ProjectCommunication::create([
            'project_id' => $project->id,
            'user_id' => $request->user()->id,
            'request_type' => $request->validated()['request_type'],
            'message' => $request->validated()['message'],
            'status' => ProjectCommunication::STATUS_PENDING,
        ]);

        $recipients = User::role(['administrador', 'coordinador'])->get();
        Notification::send($recipients, new ProjectCommunicationNotification(
            $project,
            $request->user(),
            $communication->message,
        ));

        return response()->json([
            'message' => 'Communication sent successfully',
            'communication' => [
                'id' => $communication->id,
                'request_type' => $communication->request_type,
                'message' => $communication->message,
            ],
        ], 201);
    }
}