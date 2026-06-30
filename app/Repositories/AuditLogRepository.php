<?php

namespace App\Repositories;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Repositories\Interfaces\AuditLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuditLogRepository implements AuditLogRepositoryInterface
{
    public function __construct(
        private readonly AuditLog $model
    ) {}

    /**
     * Create an immutable audit log entry.
     * All parameters are explicitly typed — no raw array to prevent misuse.
     */
    public function log(
        AuditAction $action,
        string $entityType,
        ?int $entityId,
        string $description,
        ?int $userId = null,
        ?array $metadata = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): AuditLog {
        return $this->model->create([
            'user_id'     => $userId,
            'action'      => $action->value,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'description' => $description,
            'metadata'    => $metadata,
            'ip_address'  => $ipAddress,
            'user_agent'  => $userAgent,
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters  Keys: action, user_id, entity_type, date_from, date_to, search
     */
    public function paginate(int $perPage, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['user:id,name,email'])
            ->orderBy('created_at', 'desc');

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['entity_type'])) {
            $query->where('entity_type', $filters['entity_type']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('description', 'ILIKE', "%{$term}%")
                  ->orWhere('action', 'ILIKE', "%{$term}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?AuditLog
    {
        return $this->model->with(['user:id,name,email'])->find($id);
    }
}
