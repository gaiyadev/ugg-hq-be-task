<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Services\PermissionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly PermissionService $permissionService,
    ) {}

    /**
     * GET /api/permissions
     * Returns a flat list of all permissions.
     */
    public function index(): JsonResponse
    {
        $permissions = $this->permissionService->all();

        return $this->success(
            PermissionResource::collection($permissions),
            'Permissions retrieved successfully.'
        );
    }

    /**
     * GET /api/permissions/grouped
     * Returns permissions grouped by feature area — used for the UI permission matrix.
     *
     * Response shape:
     * {
     *   "Users": [...permissions],
     *   "Resources": [...permissions],
     *   ...
     * }
     */
    public function grouped(): JsonResponse
    {
        $grouped = $this->permissionService->allGrouped();

        return $this->success($grouped, 'Grouped permissions retrieved successfully.');
    }
}
