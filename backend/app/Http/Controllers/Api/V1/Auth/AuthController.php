<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Handle user login request.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        $user = $this->authService->authenticate(
            $credentials,
            $request->boolean('remember')
        );

        $this->authService->updateLastLogin($user);

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login successful',
            'user' => new UserResource($user->load('roles.permissions')),
        ]);
    }

    /**
     * Handle user logout request.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get the authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $this->authService->getCurrentUser();

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }
}
