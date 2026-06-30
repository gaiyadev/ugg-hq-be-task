<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;

    public function findByEmail(string $email): ?User;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $perPage, array $filters = []): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User;

    public function delete(User $user): bool;

    public function assignRole(User $user, string $roleId): void;

    public function removeRole(User $user, string $roleId): void;

    public function syncRoles(User $user, array $roleIds): void;
}
