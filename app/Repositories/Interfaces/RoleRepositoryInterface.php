<?php

namespace App\Repositories\Interfaces;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface RoleRepositoryInterface
{
    public function findById(int $id): ?Role;

    public function findBySlug(string $slug): ?Role;

    public function all(): Collection;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $perPage, array $filters = []): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Role;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Role $role, array $data): Role;

    public function delete(Role $role): bool;

    public function syncPermissions(Role $role, array $permissionIds): void;

    public function assignPermission(Role $role, int $permissionId): void;

    public function removePermission(Role $role, int $permissionId): void;
}
