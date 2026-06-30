<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\Resource\ResourceDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Resource\CreateResourceRequest;
use App\Http\Requests\Resource\UpdateResourceRequest;
use App\Http\Resources\ResourceResource;
use App\Services\ResourceService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ResourceController
 *
 * Full CRUD + workflow action endpoints (submit, approve, reject).
 * Status transitions are enforced by ResourceService state machine.
 */
class ResourceController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ResourceService $resourceService,
    ) {}

    /**
     * GET /api/resources
     * Supports: ?search=, ?status=, ?created_by=, ?sort_by=, ?sort_dir=, ?with_trashed=, ?per_page=
     *
     * Scoping rules:
     *  - super-admin / admin / manager → see ALL resources (may pass ?created_by= to filter)
     *  - user / viewer                 → forced to their own resources only
     */
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $authUser */
        $authUser = $request->user();

        $filters = $request->only(['search', 'status', 'created_by', 'sort_by', 'sort_dir', 'with_trashed']);

        // If the user lacks elevated access, lock created_by to their own ID
        // so they can never view another user's resources.
        $canViewAll = $authUser->hasRole(['super-admin', 'admin', 'manager']);
        if (!$canViewAll) {
            $filters['created_by'] = $authUser->id;
        }

        $paginator = $this->resourceService->paginate(
            perPage: (int) $request->query('per_page', 15),
            filters: $filters,
        );

        $paginator->getCollection()->transform(fn($resource) => new ResourceResource($resource));

        return $this->paginated($paginator, 'Resources retrieved successfully.');
    }

    /**
     * POST /api/resources
     */
    public function store(CreateResourceRequest $request): JsonResponse
    {
        $resource = $this->resourceService->create(
            dto:     ResourceDTO::fromRequest($request->validated()),
            creator: $request->user(),
        );

        return $this->created(
            new ResourceResource($resource->load(['creator', 'updater'])),
            'Resource created successfully.'
        );
    }

    /**
     * GET /api/resources/{id}
     */
    public function show(string $id): JsonResponse
    {
        $resource = $this->resourceService->findOrFail($id);

        return $this->success(
            new ResourceResource($resource),
            'Resource retrieved successfully.'
        );
    }

    /**
     * PUT /api/resources/{id}
     */
    public function update(UpdateResourceRequest $request, string $id): JsonResponse
    {
        $resource = $this->resourceService->update(
            id:      $id,
            dto:     ResourceDTO::fromRequest($request->validated(), $request->user()->id),
            updater: $request->user(),
        );

        return $this->success(
            new ResourceResource($resource),
            'Resource updated successfully.'
        );
    }

    /**
     * DELETE /api/resources/{id}
     * Soft delete — recoverable.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->resourceService->delete($id, $request->user());

        return $this->success(null, 'Resource deleted successfully.');
    }

    /**
     * POST /api/resources/{id}/submit
     * Transitions: draft → pending
     */
    public function submit(Request $request, string $id): JsonResponse
    {
        $resource = $this->resourceService->submit($id, $request->user());

        return $this->success(
            new ResourceResource($resource),
            'Resource submitted for review.'
        );
    }

    /**
     * POST /api/resources/{id}/approve
     * Transitions: pending → approved
     * Generates SHA256 signature.
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        $resource = $this->resourceService->approve($id, $request->user());

        return $this->success(
            new ResourceResource($resource),
            'Resource approved successfully.'
        );
    }

    /**
     * POST /api/resources/{id}/reject
     * Transitions: pending → rejected
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $resource = $this->resourceService->reject($id, $request->user());

        return $this->success(
            new ResourceResource($resource),
            'Resource rejected.'
        );
    }

    /**
     * GET /api/resources/{id}/verify-signature
     * Verify the SHA256 signature on an approved resource.
     */
    public function verifySignature(string $id): JsonResponse
    {
        $isValid = $this->resourceService->verifySignature($id);
        $resource = $this->resourceService->findOrFail($id);

        return $this->success([
            'resource_id' => $id,
            'signature'   => $resource->signature,
            'verified'    => $isValid,
            'verified_at' => now()->toIso8601String(),
        ], $isValid ? 'Signature is valid.' : 'Signature verification failed — data may have been tampered.');
    }
}
