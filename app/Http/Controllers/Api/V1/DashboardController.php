<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    /**
     * GET /api/dashboard
     * Returns aggregated stats for the dashboard widget grid.
     */
    public function index(): JsonResponse
    {
        return $this->success(
            $this->dashboardService->getStats(),
            'Dashboard data retrieved successfully.'
        );
    }
}
