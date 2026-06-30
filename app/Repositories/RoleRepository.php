<?php

namespace App\Repositories;

use App\Models\Role;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository implements RoleRepositoryInterface
{
    public function __construct(
        private readonly Role $model
    ) {}

    public function findById(int $id): ?Role
    {
        return $this->model->with(['permissions:id,name,slug,group'])->find($id);
    }

    public function findBySlug(string $slug): ?Role
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function all(): Collection
    {
        return $this->model->with(['permissions:id,name,slug,group'])
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $perPage, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with(['permissions:id,name,slug,group'])
            ->withCount('users');

        if (!empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('name', 'ILIKE', "%{$term}%")
                  ->orWhere('slug', 'ILIKE', "%{$term}%");
            });
        }

        $sortBy  = in_array($filters['sort_by'] ?? '', ['name', 'slug', 'created_at'])
            ? $filters['sort_by']
            : 'created_at';
        $sortDir = ($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Role
    {
        return $this->model->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Role $role, array $data): Role
    {
        $role->update($data);
        return $role->refresh()->load(['permissions:id,name,slug,group']);
    }

    public function delete(Role $role): bool
    {
        return (bool) $role->delete();
    }

    public function syncPermissions(Role $role, array $permissionIds): void
    {
        $role->permissions()->sync($permissionIds);
    }

    public function assignPermission(Role $role, int $permissionId): void
    {
        $role->permissions()->syncWithoutDetaching([$permissionId]);
    }

    public function removePermission(Role $role, int $permissionId): void
    {
        $role->permissions()->detach($permissionId);
    }
}
