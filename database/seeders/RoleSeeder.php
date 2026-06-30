<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Seed system roles and assign permissions.
     *
     * Role hierarchy:
     * - super-admin  → ALL permissions (system role, cannot be deleted)
     * - admin        → All except system-level ops
     * - manager      → Resources + limited user view
     * - viewer       → Read-only everywhere
     * - user         → Resources only (own resources)
     */
    public function run(): void
    {
        $roles = [
            [
                'name'        => 'Super Admin',
                'slug'        => 'super-admin',
                'description' => 'Full unrestricted system access',
                'is_system'   => true,
                'permissions' => '*', // All permissions
            ],
            [
                'name'        => 'Admin',
                'slug'        => 'admin',
                'description' => 'Administrative access to all management functions',
                'is_system'   => true,
                'permissions' => [
                    'users.view', 'users.create', 'users.update', 'users.delete',
                    'roles.view', 'roles.create', 'roles.update', 'roles.assign',
                    'permissions.view', 'permissions.assign',
                    'resources.view', 'resources.create', 'resources.update',
                    'resources.delete', 'resources.approve',
                    'audit.view',
                    'dashboard.view',
                ],
            ],
            [
                'name'        => 'Manager',
                'slug'        => 'manager',
                'description' => 'Resource management and limited user visibility',
                'is_system'   => false,
                'permissions' => [
                    'users.view',
                    'roles.view',
                    'resources.view', 'resources.create', 'resources.update', 'resources.approve',
                    'audit.view',
                    'dashboard.view',
                ],
            ],
            [
                'name'        => 'Viewer',
                'slug'        => 'viewer',
                'description' => 'Read-only access to all sections',
                'is_system'   => false,
                'permissions' => [
                    'users.view',
                    'roles.view',
                    'permissions.view',
                    'resources.view',
                    'audit.view',
                    'dashboard.view',
                ],
            ],
            [
                'name'        => 'User',
                'slug'        => 'user',
                'description' => 'Standard user — can create and manage own resources',
                'is_system'   => false,
                'permissions' => [
                    'resources.view',
                    'resources.create',
                    'resources.update',
                    'dashboard.view',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);

            /** @var Role $role */
            $role = Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );

            // Assign permissions
            if ($permissions === '*') {
                $role->permissions()->sync(Permission::pluck('id'));
            } else {
                $permissionIds = Permission::whereIn('slug', $permissions)->pluck('id');
                $role->permissions()->sync($permissionIds);
            }
        }

        $this->command->info('✅ Roles seeded: ' . count($roles) . ' roles with permissions assigned');
    }
}
