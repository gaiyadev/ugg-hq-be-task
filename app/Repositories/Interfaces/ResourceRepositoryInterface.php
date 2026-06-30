<?php

namespace App\Repositories\Interfaces;

use App\Models\Resource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ResourceRepositoryInterface
{
    public function findById(int $id): ?Resource;

    public function findByIdWithTrashed(int $id): ?Resource;

    /**
     * @param  array<string, mixed>  $filters  Keys: status, search, sort_by, sort_dir, created_by
     */
    public function paginate(int $perPage, array $filters = []): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Resource;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Resource $resource, array $data): Resource;

    public function delete(Resource $resource): bool;

    public function restore(int $id): bool;

    public function forceDelete(int $id): bool;
}
