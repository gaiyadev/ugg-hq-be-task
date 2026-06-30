<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Repositories\Interfaces\AuditLogRepositoryInterface;
use Illuminate\Http\Request;

/**
 * AuditLogService
 *
 * Central service for writing audit logs from anywhere in the application.
 * Used by event listeners so business services stay audit-agnostic.
 * Can also be injected directly in controllers when no event is suitable.
 */
class AuditLogService
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $auditLogRepository
    ) {}

    /**
     * Write an audit log entry, automatically capturing request context.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public function log(
        AuditAction $action,
        string $entityType,
        ?int $entityId,
        string $description,
        ?int $userId = null,
        ?array $metadata = null,
        ?Request $request = null
    ): AuditLog {
        $ip        = $request?->ip();
        $userAgent = $request?->userAgent();

        // Use authenticated user if userId not explicitly provided
        if ($userId === null && auth()->check()) {
            $userId = auth()->id();
        }

        return $this->auditLogRepository->log(
            action:      $action,
            entityType:  $entityType,
            entityId:    $entityId,
            description: $description,
            userId:      $userId,
            metadata:    $metadata,
            ipAddress:   $ip,
            userAgent:   $userAgent,
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $perPage = 20, array $filters = [])
    {
        return $this->auditLogRepository->paginate($perPage, $filters);
    }

    public function findById(int $id): ?AuditLog
    {
        return $this->auditLogRepository->findById($id);
    }
}
