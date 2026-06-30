<?php

namespace App\Repositories\Interfaces;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AuditLogRepositoryInterface
{
    /**
     * Create an immutable audit log entry.
     *
     * @param  array<string, mixed>  $data
     */
    public function log(
        AuditAction $action,
        string $entityType,
        ?string $entityId,
        string $description,
        ?string $userId = null,
        ?array $metadata = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): AuditLog;

    /**
     * @param  array<string, mixed>  $filters  Keys: action, user_id, entity_type, date_from, date_to, search
     */
    public function paginate(int $perPage, array $filters = []): LengthAwarePaginator;

    public function findById(string $id): ?AuditLog;
}
