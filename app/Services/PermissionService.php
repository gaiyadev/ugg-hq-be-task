<?php

namespace App\Services;

use App\Repositories\Interfaces\PermissionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PermissionService
{
    public function __construct(
        private readonly PermissionRepositoryInterface $permissionRepository,
    ) {}

    public function all(): Collection
    {
        return $this->permissionRepository->all();
    }

    /**
     * Return permissions grouped by feature area for UI matrix rendering.
     *
     * @return array<string, Collection>
     */
    public function allGrouped(): array
    {
        return $this->permissionRepository->allGrouped();
    }
}
