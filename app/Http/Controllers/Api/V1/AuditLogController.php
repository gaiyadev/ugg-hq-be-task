<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Services\AuditLogService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * GET /api/audit-logs
     * Supports: ?action=, ?user_id=, ?entity_type=, ?date_from=, ?date_to=, ?search=, ?per_page=
     * Sorted by created_at DESC — newest first (immutable, no pagination of historical data).
     */
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->auditLogService->paginate(
            perPage: (int) $request->query('per_page', 20),
            filters: $request->only(['action', 'user_id', 'entity_type', 'date_from', 'date_to', 'search']),
        );

        $paginator->getCollection()->transform(fn($log) => new AuditLogResource($log));

        return $this->paginated($paginator, 'Audit logs retrieved successfully.');
    }

    /**
     * GET /api/audit-logs/{id}
     */
    public function show(string $id): JsonResponse
    {
        $log = $this->auditLogService->findById($id);

        if (!$log) {
            return $this->notFound('Audit log entry not found.');
        }

        return $this->success(new AuditLogResource($log), 'Audit log retrieved successfully.');
    }
}
