<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\CreateRoleRequest;
use App\Http\Requests\Role\SyncPermissionsRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly RoleService $roleService,
    ) {}

    /**
     * GET /api/roles
     * Supports: ?search=, ?sort_by=, ?sort_dir=, ?per_page=
     */
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->roleService->paginate(
            perPage: (int) $request->query('per_page', 15),
            filters: $request->only(['search', 'sort_by', 'sort_dir']),
        );

        $paginator->getCollection()->transform(fn($role) => new RoleResource($role));

        return $this->paginated($paginator, 'Roles retrieved successfully.');
    }

    /**
     * POST /api/roles
     */
    public function store(CreateRoleRequest $request): JsonResponse
    {
        $validated      = $request->validated();
        $permissionIds  = $validated['permission_ids'] ?? [];
        unset($validated['permission_ids']);

        $role = $this->roleService->create($validated);

        if (!empty($permissionIds)) {
            $this->roleService->syncPermissions($role->id, $permissionIds);
            $role->load('permissions');
        }

        return $this->created(new RoleResource($role), 'Role created successfully.');
    }

    /**
     * GET /api/roles/{id}
     */
    public function show(string $id): JsonResponse
    {
        $role = $this->roleService->findOrFail($id);

        return $this->success(new RoleResource($role), 'Role retrieved successfully.');
    }

    /**
     * PUT /api/roles/{id}
     */
    public function update(UpdateRoleRequest $request, string $id): JsonResponse
    {
        $role = $this->roleService->update($id, $request->validated());

        return $this->success(new RoleResource($role), 'Role updated successfully.');
    }

    /**
     * DELETE /api/roles/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $this->roleService->delete($id);

        return $this->success(null, 'Role deleted successfully.');
    }

    /**
     * POST /api/roles/{id}/permissions
     * Body: { permission_ids: [1, 2, 3] }
     * Replaces ALL permissions on the role.
     */
    public function syncPermissions(SyncPermissionsRequest $request, string $id): JsonResponse
    {
        $role = $this->roleService->syncPermissions($id, $request->validated('permission_ids'));

        return $this->success(new RoleResource($role), 'Permissions synced successfully.');
    }
}
