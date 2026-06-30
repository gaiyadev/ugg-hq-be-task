<?php

namespace App\Repositories\Interfaces;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

interface PermissionRepositoryInterface
{
    public function findById(int $id): ?Permission;

    public function findBySlug(string $slug): ?Permission;

    public function all(): Collection;

    /**
     * Return permissions grouped by their 'group' field.
     *
     * @return array<string, Collection>
     */
    public function allGrouped(): array;
}
