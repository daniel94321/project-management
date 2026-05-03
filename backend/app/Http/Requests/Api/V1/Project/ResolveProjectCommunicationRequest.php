<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResolveProjectCommunicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && (
            $user->hasRole('administrador') ||
            $user->hasRole('coordinador') ||
            $user->hasRole('director') ||
            $user->hasRole('evaluador')
        );
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['approved', 'changes_requested'])],
            'response' => ['nullable', 'string', 'max:1000'],
        ];
    }
}