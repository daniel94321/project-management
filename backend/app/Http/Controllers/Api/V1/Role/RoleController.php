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
     * Return all roles (used by forms to populate role selectors).
     */
    public function index(): AnonymousResourceCollection
    {
        $roles = Role::orderBy('name')->get();

        return RoleResource::collection($roles);
    }
}
