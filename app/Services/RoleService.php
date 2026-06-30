<?php

namespace App\Services;

use App\Events\Rbac\PermissionChanged;
use App\Models\Role;
use App\Repositories\Interfaces\PermissionRepositoryInterface;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class RoleService
{
    public function __construct(
        private readonly RoleRepositoryInterface $roleRepository,
        private readonly PermissionRepositoryInterface $permissionRepository,
    ) {}

    public function all(): Collection
    {
        return $this->roleRepository->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $perPage, array $filters = []): LengthAwarePaginator
    {
        return $this->roleRepository->paginate($perPage, $filters);
    }

    public function findOrFail(int $id): Role
    {
        $role = $this->roleRepository->findById($id);

        if (!$role) {
            abort(404, "Role with ID [{$id}] not found.");
        }

        return $role;
    }

    /**
     * Create a new role. Auto-generates slug from name.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Role
    {
        $data['slug'] = Str::slug($data['name']);
        return $this->roleRepository->create($data);
    }

    /**
     * Update a non-system role.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): Role
    {
        $role = $this->findOrFail($id);

        if ($role->is_system && isset($data['is_system'])) {
            unset($data['is_system']); // Prevent toggling system flag
        }

        // Prevent slug change on system roles
        if ($role->is_system) {
            unset($data['slug']);
        } elseif (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $this->roleRepository->update($role, $data);
    }

    /**
     * Delete a role. System roles are protected.
     *
     * @throws \Exception
     */
    public function delete(int $id): bool
    {
        $role = $this->findOrFail($id);

        if ($role->is_system) {
            abort(403, "System role [{$role->name}] cannot be deleted.");
        }

        return $this->roleRepository->delete($role);
    }

    /**
     * Sync permissions for a role.
     *
     * @param  int[]  $permissionIds
     */
    public function syncPermissions(int $roleId, array $permissionIds): Role
    {
        $role = $this->findOrFail($roleId);
        $this->roleRepository->syncPermissions($role, $permissionIds);

        event(new PermissionChanged($role, auth()->user()));

        return $this->roleRepository->findById($roleId);
    }
}
