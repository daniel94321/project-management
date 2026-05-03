<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Project\StoreProjectCommunicationRequest;
use App\Http\Resources\Api\V1\ProjectCommunicationResource;
use App\Models\Project;
use App\Models\ProjectCommunication;
use App\Models\User;
use App\Notifications\ProjectCommunicationNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Notification;

class ProjectCommunicationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        abort_unless($user && ($user->hasRole('administrador') || $user->hasRole('coordinador')), 403);

        $communications = ProjectCommunication::with(['project.owner', 'user'])
            ->latest()
            ->paginate(10);

        return ProjectCommunicationResource::collection($communications);
    }

    public function store(StoreProjectCommunicationRequest $request, Project $project): JsonResponse
    {
        $communication = ProjectCommunication::create([
            'project_id' => $project->id,
            'user_id' => $request->user()->id,
            'message' => $request->validated()['message'],
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
                'message' => $communication->message,
            ],
        ], 201);
    }
}