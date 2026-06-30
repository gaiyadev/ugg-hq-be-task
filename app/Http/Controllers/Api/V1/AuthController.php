<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\RegisterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * AuthController
 *
 * Handles all authentication endpoints.
 * Thin: validates → delegates to AuthService → transforms → responds.
 * No business logic here.
 */
class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * POST /api/auth/register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register(
            dto:     RegisterDTO::fromRequest($request->validated()),
            request: $request,
        );

        return $this->created([
            'user'         => new UserResource($result['user']),
            'access_token' => $result['token'],
            'token_type'   => 'Bearer',
        ], 'Account created successfully. Welcome to UGG-HQ!');
    }

    /**
     * POST /api/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            dto:     LoginDTO::fromRequest($request->validated()),
            request: $request,
        );

        return $this->success([
            'user'         => new UserResource($result['user']),
            'access_token' => $result['token'],
            'token_type'   => 'Bearer',
        ], 'Login successful.');
    }

    /**
     * POST /api/auth/logout
     * Requires: auth:sanctum
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->success(null, 'Logged out successfully.');
    }

    /**
     * POST /api/auth/refresh
     * Requires: auth:sanctum
     * Issues a new token, revokes the current one.
     */
    public function refresh(Request $request): JsonResponse
    {
        $result = $this->authService->refresh($request->user());

        return $this->success([
            'user'         => new UserResource($result['user']),
            'access_token' => $result['token'],
            'token_type'   => 'Bearer',
        ], 'Token refreshed successfully.');
    }

    /**
     * GET /api/auth/me
     * Requires: auth:sanctum
     * Returns the authenticated user with their roles and permissions.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles.permissions');

        return $this->success(
            new UserResource($user),
            'Authenticated user retrieved successfully.'
        );
    }

    /**
     * POST /api/auth/forgot-password
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $message = $this->authService->forgotPassword($request->validated('email'));

        return $this->success(null, $message);
    }

    /**
     * POST /api/auth/reset-password
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $message = $this->authService->resetPassword(
            token:    $validated['token'],
            email:    $validated['email'],
            password: $validated['password'],
        );

        return $this->success(null, $message);
    }
}
