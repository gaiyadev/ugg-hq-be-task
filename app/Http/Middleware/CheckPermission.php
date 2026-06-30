<?php

namespace App\Http\Middleware;

use App\Enums\AuditAction;
use App\Repositories\Interfaces\AuditLogRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckPermission Middleware
 *
 * Usage in routes:
 *   ->middleware('permission:users.create')
 *   ->middleware('permission:resources.approve')
 *
 * If the authenticated user does not have the specified permission
 * (via any of their assigned roles), a 403 is returned and the
 * denied access event is logged.
 */
class CheckPermission
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (!$user->hasPermission($permission)) {
            // Log the denied access attempt
            $this->auditLogRepository->log(
                action:      AuditAction::AccessDenied,
                entityType:  'System',
                entityId:    null,
                description: "Access denied for [{$user->email}] on permission [{$permission}].",
                userId:      $user->id,
                metadata:    [
                    'permission' => $permission,
                    'url'        => $request->fullUrl(),
                    'method'     => $request->method(),
                ],
                ipAddress:   $request->ip(),
                userAgent:   $request->userAgent(),
            );

            return response()->json([
                'success' => false,
                'message' => "You do not have the required permission: [{$permission}].",
            ], 403);
        }

        return $next($request);
    }
}
