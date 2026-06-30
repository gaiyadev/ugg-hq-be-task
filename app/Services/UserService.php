<?php

namespace App\Services;

use App\Events\Rbac\PermissionChanged;
use App\Events\Rbac\RoleAssigned;
use App\Exceptions\Permission\InsufficientPermissionException;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly RoleRepositoryInterface $roleRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $perPage, array $filters = []): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage, $filters);
    }

    public function findOrFail(int $id): User
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            abort(404, "User with ID [{$id}] not found.");
        }

        return $user;
    }

    /**
     * Create a new user.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        return $this->userRepository->create($data);
    }

    /**
     * Update an existing user.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): User
    {
        $user = $this->findOrFail($id);
        return $this->userRepository->update($user, $data);
    }

    public function delete(int $id): bool
    {
        $user = $this->findOrFail($id);
        return $this->userRepository->delete($user);
    }

    /**
     * Assign a role to a user. Prevents assigning system roles without super-admin.
     *
     * @throws \App\Exceptions\Permission\InsufficientPermissionException
     */
    public function assignRole(int $userId, int $roleId, User $actor): void
    {
        $user = $this->findOrFail($userId);
        $role = $this->roleRepository->findById($roleId);

        if (!$role) {
            abort(404, "Role with ID [{$roleId}] not found.");
        }

        // Only super-admin can assign the super-admin role
        if ($role->slug === 'super-admin' && !$actor->hasRole('super-admin')) {
            throw new InsufficientPermissionException('super-admin role assignment');
        }

        $this->userRepository->assignRole($user, $roleId);

        event(new RoleAssigned($user, $role, $actor));
    }

    /**
     * Remove a role from a user.
     */
    public function removeRole(int $userId, int $roleId, User $actor): void
    {
        $user = $this->findOrFail($userId);
        $role = $this->roleRepository->findById($roleId);

        if (!$role) {
            abort(404, "Role with ID [{$roleId}] not found.");
        }

        $this->userRepository->removeRole($user, $roleId);

        event(new RoleAssigned($user, $role, $actor));
    }
}
