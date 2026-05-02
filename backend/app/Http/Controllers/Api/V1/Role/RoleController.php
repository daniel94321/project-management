<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Role;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\RoleResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Roles enabled in this application.
     *
     * @var array<int, string>
     */
    private array $allowedRoles = [
        'administrador',
        'coordinador',
        'evaluador',
        'director',
        'estudiante',
    ];

    /**
     * Return all roles (used by forms to populate role selectors).
     */
    public function index(): AnonymousResourceCollection
    {
        $roles = Role::query()
            ->whereIn('name', $this->allowedRoles)
            ->orderBy('name')
            ->get();

        return RoleResource::collection($roles);
    }
}
