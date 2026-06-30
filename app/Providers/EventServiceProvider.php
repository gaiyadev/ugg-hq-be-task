<?php

namespace App\Providers;

use App\Events\Auth\UserLoggedIn;
use App\Events\Auth\UserLoggedOut;
use App\Events\Rbac\PermissionChanged;
use App\Events\Rbac\RoleAssigned;
use App\Events\Resource\ResourceCreated;
use App\Events\Resource\ResourceDeleted;
use App\Events\Resource\ResourceUpdated;
use App\Listeners\AuditLogListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Map events to their listeners.
     * All auditable events route to AuditLogListener with specific handler methods.
     *
     * @var array<class-string, array<int, class-string|string>>
     */
    protected $listen = [
        // ─── Auth Events ─────────────────────────────────────────────────
        UserLoggedIn::class  => [
            [AuditLogListener::class, 'handleUserLoggedIn'],
        ],
        UserLoggedOut::class => [
            [AuditLogListener::class, 'handleUserLoggedOut'],
        ],

        // ─── Resource Events ──────────────────────────────────────────────
        ResourceCreated::class => [
            [AuditLogListener::class, 'handleResourceCreated'],
        ],
        ResourceUpdated::class => [
            [AuditLogListener::class, 'handleResourceUpdated'],
        ],
        ResourceDeleted::class => [
            [AuditLogListener::class, 'handleResourceDeleted'],
        ],

        // ─── RBAC Events ──────────────────────────────────────────────────
        RoleAssigned::class => [
            [AuditLogListener::class, 'handleRoleAssigned'],
        ],
        PermissionChanged::class => [
            [AuditLogListener::class, 'handlePermissionChanged'],
        ],
    ];

    public function boot(): void
    {
        //
    }

    /**
     * Explicit mappings above; disable auto-discovery to prevent duplicates.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
