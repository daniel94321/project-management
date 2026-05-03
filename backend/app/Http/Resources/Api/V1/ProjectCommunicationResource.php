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
            'project' => [
                'id' => $this->project->id,
                'name' => $this->project->name,
                'status' => $this->project->status,
                'priority' => $this->project->priority,
            ],
            'sender' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'message' => $this->message,
            'created_at' => $this->created_at,
        ];
    }
}