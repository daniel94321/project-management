<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Project;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectCommunicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $project = $this->route('project');

        if (! $user || ! $project instanceof Project) {
            return false;
        }

        return $user->id === $project->owner_id || $user->hasRole('estudiante');
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:1000'],
        ];
    }
}