<?php

namespace App\Services;

use App\Repositories\Interfaces\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResourceRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;

/**
 * DashboardService
 *
 * Aggregates data from multiple repositories for dashboard metrics.
 * Kept in a single service so controllers stay thin.
 */
class DashboardService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly ResourceRepositoryInterface $resourceRepository,
        private readonly AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * Get dashboard statistics.
     *
     * @return array<string, mixed>
     */
    public function getStats(): array
    {
        // Recent audit entries (last 10)
        $recentActivity = $this->auditLogRepository->paginate(10, []);

        return [
            'users' => [
                'total'  => \App\Models\User::count(),
                'active' => \App\Models\User::where('status', 'active')->count(),
            ],
            'resources' => [
                'total'    => \App\Models\Resource::count(),
                'draft'    => \App\Models\Resource::where('status', 'draft')->count(),
                'pending'  => \App\Models\Resource::where('status', 'pending')->count(),
                'approved' => \App\Models\Resource::where('status', 'approved')->count(),
                'rejected' => \App\Models\Resource::where('status', 'rejected')->count(),
            ],
            'recent_activity' => $recentActivity->items(),
        ];
    }
}
