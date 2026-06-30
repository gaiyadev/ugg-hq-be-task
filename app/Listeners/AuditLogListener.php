<?php

namespace App\Listeners;

use App\Enums\AuditAction;
use App\Events\Auth\UserLoggedIn;
use App\Events\Auth\UserLoggedOut;
use App\Events\Rbac\PermissionChanged;
use App\Events\Rbac\RoleAssigned;
use App\Events\Resource\ResourceCreated;
use App\Events\Resource\ResourceDeleted;
use App\Events\Resource\ResourceUpdated;
use App\Repositories\Interfaces\AuditLogRepositoryInterface;

/**
 * AuditLogListener
 *
 * Single listener that handles ALL auditable events.
 * Registered in EventServiceProvider for each relevant event.
 *
 * This approach keeps audit logic completely out of business services —
 * services just dispatch events, this listener handles the logging.
 */
class AuditLogListener
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    // ─── Auth Events ──────────────────────────────────────────────────────

    public function handleUserLoggedIn(UserLoggedIn $event): void
    {
        $this->auditLogRepository->log(
            action:      AuditAction::Login,
            entityType:  'User',
            entityId:    $event->user->id,
            description: "User [{$event->user->email}] logged in.",
            userId:      $event->user->id,
            metadata:    null,
            ipAddress:   $event->ipAddress,
            userAgent:   $event->userAgent,
        );
    }

    public function handleUserLoggedOut(UserLoggedOut $event): void
    {
        $this->auditLogRepository->log(
            action:      AuditAction::Logout,
            entityType:  'User',
            entityId:    $event->user->id,
            description: "User [{$event->user->email}] logged out.",
            userId:      $event->user->id,
        );
    }

    // ─── Resource Events ──────────────────────────────────────────────────

    public function handleResourceCreated(ResourceCreated $event): void
    {
        $this->auditLogRepository->log(
            action:      AuditAction::ResourceCreated,
            entityType:  'Resource',
            entityId:    $event->resource->id,
            description: "Resource [{$event->resource->title}] was created.",
            userId:      $event->actor->id,
            metadata:    ['title' => $event->resource->title, 'status' => $event->resource->status],
        );
    }

    public function handleResourceUpdated(ResourceUpdated $event): void
    {
        $resource = $event->resource;

        // Determine the specific audit action based on status
        $action = match($resource->status) {
            'approved' => AuditAction::ResourceApproved,
            'rejected' => AuditAction::ResourceRejected,
            'pending'  => AuditAction::ResourceSubmitted,
            default    => AuditAction::ResourceUpdated,
        };

        $descriptions = [
            'approved' => "Resource [{$resource->title}] was approved.",
            'rejected' => "Resource [{$resource->title}] was rejected.",
            'pending'  => "Resource [{$resource->title}] was submitted for review.",
        ];

        $description = $descriptions[$resource->status]
            ?? "Resource [{$resource->title}] was updated.";

        $this->auditLogRepository->log(
            action:      $action,
            entityType:  'Resource',
            entityId:    $resource->id,
            description: $description,
            userId:      $event->actor->id,
            metadata:    [
                'status'    => $resource->status,
                'signature' => $resource->signature,
            ],
        );
    }

    public function handleResourceDeleted(ResourceDeleted $event): void
    {
        $this->auditLogRepository->log(
            action:      AuditAction::ResourceDeleted,
            entityType:  'Resource',
            entityId:    $event->resource->id,
            description: "Resource [{$event->resource->title}] was deleted.",
            userId:      $event->actor->id,
        );
    }

    // ─── RBAC Events ──────────────────────────────────────────────────────

    public function handleRoleAssigned(RoleAssigned $event): void
    {
        $this->auditLogRepository->log(
            action:      AuditAction::RoleAssigned,
            entityType:  'User',
            entityId:    $event->targetUser->id,
            description: "Role [{$event->role->name}] assigned to [{$event->targetUser->email}] by [{$event->actor->email}].",
            userId:      $event->actor->id,
            metadata:    ['role_id' => $event->role->id, 'role_slug' => $event->role->slug],
        );
    }

    public function handlePermissionChanged(PermissionChanged $event): void
    {
        $actorEmail = $event->actor?->email ?? 'system';

        $this->auditLogRepository->log(
            action:      AuditAction::PermissionAssigned,
            entityType:  'Role',
            entityId:    $event->role->id,
            description: "Permissions updated for role [{$event->role->name}] by [{$actorEmail}].",
            userId:      $event->actor?->id,
            metadata:    ['role_id' => $event->role->id, 'role_slug' => $event->role->slug],
        );
    }
}
