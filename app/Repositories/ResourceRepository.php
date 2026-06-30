<?php

namespace App\Repositories;

use App\Models\Resource;
use App\Repositories\Interfaces\ResourceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ResourceRepository implements ResourceRepositoryInterface
{
    public function __construct(
        private readonly Resource $model
    ) {}

    public function findById(string $id): ?Resource
    {
        return $this->model
            ->with(['creator:id,name,email', 'updater:id,name,email', 'approver:id,name,email'])
            ->find($id);
    }

    public function findByIdWithTrashed(string $id): ?Resource
    {
        return $this->model->withTrashed()
            ->with(['creator:id,name,email'])
            ->find($id);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $perPage, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model
            ->with(['creator:id,name,email', 'approver:id,name,email']);

        // Full-text search on title and description
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->withStatus($filters['status']);
        }

        // Filter by creator
        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        // Include soft-deleted records (admin only)
        if (!empty($filters['with_trashed'])) {
            $query->withTrashed();
        }

        // Sorting — whitelist prevents SQL injection
        $allowedSorts = ['title', 'status', 'created_at', 'updated_at', 'approved_at'];
        $sortBy  = in_array($filters['sort_by'] ?? '', $allowedSorts)
            ? $filters['sort_by']
            : 'created_at';
        $sortDir = ($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Resource
    {
        return $this->model->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Resource $resource, array $data): Resource
    {
        $resource->update($data);
        return $resource->refresh()->load(['creator:id,name,email', 'updater:id,name,email', 'approver:id,name,email']);
    }

    public function delete(Resource $resource): bool
    {
        return (bool) $resource->delete();
    }

    public function restore(string $id): bool
    {
        return (bool) $this->model->withTrashed()->find($id)?->restore();
    }

    public function forceDelete(string $id): bool
    {
        $resource = $this->model->withTrashed()->find($id);
        return $resource ? (bool) $resource->forceDelete() : false;
    }
}
