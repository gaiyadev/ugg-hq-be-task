<?php

namespace App\Repositories;

use App\Models\Permission;
use App\Repositories\Interfaces\PermissionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PermissionRepository implements PermissionRepositoryInterface
{
    public function __construct(
        private readonly Permission $model
    ) {}

    public function findById(string $id): ?Permission
    {
        return $this->model->find($id);
    }

    public function findBySlug(string $slug): ?Permission
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function all(): Collection
    {
        return $this->model->orderBy('group')->orderBy('name')->get();
    }

    /**
     * Return permissions keyed by group name for UI rendering.
     *
     * @return array<string, Collection<Permission>>
     */
    public function allGrouped(): array
    {
        return $this->all()
            ->groupBy('group')
            ->toArray();
    }
}
