<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectCommunicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'request_type' => $this->request_type,
            'status' => $this->status,
            'project' => [
                'id' => $this->project->id,
                'name' => $this->project->name,
                'status' => $this->project->status,
                'priority' => $this->project->priority,
                'description' => $this->project->description,
                'start_date' => $this->project->start_date?->toDateString(),
                'end_date' => $this->project->end_date?->toDateString(),
            ],
            'sender' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'resolved_by' => $this->whenLoaded('resolvedBy', fn () => [
                'id' => $this->resolvedBy->id,
                'name' => $this->resolvedBy->name,
            ]),
            'response' => $this->response,
            'message' => $this->message,
            'resolved_at' => $this->resolved_at?->toDateTimeString(),
            'created_at' => $this->created_at,
        ];
    }
}