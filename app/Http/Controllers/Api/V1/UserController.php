<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * UserController
 *
 * REST resource controller for User management.
 * All authorization is handled by 'permission' middleware on routes.
 */
class UserController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly UserService $userService,
    ) {}

    /**
     * GET /api/users
     * Supports: ?search=, ?status=, ?role=, ?sort_by=, ?sort_dir=, ?per_page=
     */
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->userService->paginate(
            perPage:  (int) $request->query('per_page', 15),
            filters:  $request->only(['search', 'status', 'role', 'sort_by', 'sort_dir']),
        );

        // Transform each item via UserResource
        $paginator->getCollection()->transform(fn($user) => new UserResource($user));

        return $this->paginated($paginator, 'Users retrieved successfully.');
    }

    /**
     * POST /api/users
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $roleIds   = $validated['role_ids'] ?? [];
        unset($validated['role_ids']);

        $user = $this->userService->create($validated);

        // Assign roles if provided
        foreach ($roleIds as $roleId) {
            $this->userService->assignRole($user->id, $roleId, $request->user());
        }

        return $this->created(
            new UserResource($user->load('roles')),
            'User created successfully.'
        );
    }

    /**
     * GET /api/users/{id}
     */
    public function show(string $id): JsonResponse
    {
        $user = $this->userService->findOrFail($id);

        return $this->success(
            new UserResource($user),
            'User retrieved successfully.'
        );
    }

    /**
     * PUT /api/users/{id}
     */
    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $user = $this->userService->update($id, $request->validated());

        return $this->success(
            new UserResource($user),
            'User updated successfully.'
        );
    }

    /**
     * DELETE /api/users/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $this->userService->delete($id);

        return $this->success(null, 'User deleted successfully.');
    }

    /**
     * POST /api/users/{id}/roles
     * Body: { role_id: int }
     */
    public function assignRole(Request $request, string $id): JsonResponse
    {
        $request->validate(['role_id' => ['required', 'integer', 'exists:roles,id']]);

        $this->userService->assignRole($id, $request->integer('role_id'), $request->user());

        $user = $this->userService->findOrFail($id);

        return $this->success(
            new UserResource($user),
            'Role assigned successfully.'
        );
    }

    /**
     * DELETE /api/users/{id}/roles/{roleId}
     */
    public function removeRole(Request $request, string $id, string $roleId): JsonResponse
    {
        $this->userService->removeRole($id, $roleId, $request->user());

        $user = $this->userService->findOrFail($id);

        return $this->success(
            new UserResource($user),
            'Role removed successfully.'
        );
    }
}
