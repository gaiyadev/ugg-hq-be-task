<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Seed all system permissions.
     *
     * Organized by group using dot-notation slugs:
     * {resource}.{action}
     *
     * This matches the CheckPermission middleware and Policy checks.
     */
    public function run(): void
    {
        $permissions = [
            // ─── Users ───────────────────────────────────────────────────────
            ['group' => 'Users', 'name' => 'View Users',   'slug' => 'users.view',   'description' => 'View user list and details'],
            ['group' => 'Users', 'name' => 'Create Users', 'slug' => 'users.create', 'description' => 'Create new users'],
            ['group' => 'Users', 'name' => 'Update Users', 'slug' => 'users.update', 'description' => 'Update existing users'],
            ['group' => 'Users', 'name' => 'Delete Users', 'slug' => 'users.delete', 'description' => 'Soft-delete users'],

            // ─── Roles ───────────────────────────────────────────────────────
            ['group' => 'Roles', 'name' => 'View Roles',   'slug' => 'roles.view',   'description' => 'View role list and details'],
            ['group' => 'Roles', 'name' => 'Create Roles', 'slug' => 'roles.create', 'description' => 'Create new roles'],
            ['group' => 'Roles', 'name' => 'Update Roles', 'slug' => 'roles.update', 'description' => 'Update existing roles'],
            ['group' => 'Roles', 'name' => 'Delete Roles', 'slug' => 'roles.delete', 'description' => 'Delete non-system roles'],
            ['group' => 'Roles', 'name' => 'Assign Roles', 'slug' => 'roles.assign', 'description' => 'Assign/remove roles from users'],

            // ─── Permissions ─────────────────────────────────────────────────
            ['group' => 'Permissions', 'name' => 'View Permissions',   'slug' => 'permissions.view',   'description' => 'View permissions'],
            ['group' => 'Permissions', 'name' => 'Assign Permissions', 'slug' => 'permissions.assign', 'description' => 'Assign/remove permissions from roles'],

            // ─── Resources ───────────────────────────────────────────────────
            ['group' => 'Resources', 'name' => 'View Resources',    'slug' => 'resources.view',    'description' => 'View resource list and details'],
            ['group' => 'Resources', 'name' => 'Create Resources',  'slug' => 'resources.create',  'description' => 'Create new resources'],
            ['group' => 'Resources', 'name' => 'Update Resources',  'slug' => 'resources.update',  'description' => 'Update existing resources'],
            ['group' => 'Resources', 'name' => 'Delete Resources',  'slug' => 'resources.delete',  'description' => 'Soft-delete resources'],
            ['group' => 'Resources', 'name' => 'Approve Resources', 'slug' => 'resources.approve', 'description' => 'Approve or reject resource requests'],

            // ─── Audit Logs ───────────────────────────────────────────────────
            ['group' => 'Audit', 'name' => 'View Audit Logs', 'slug' => 'audit.view', 'description' => 'View audit log entries'],

            // ─── Dashboard ────────────────────────────────────────────────────
            ['group' => 'Dashboard', 'name' => 'View Dashboard', 'slug' => 'dashboard.view', 'description' => 'Access the main dashboard'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        $this->command->info('✅ Permissions seeded: ' . count($permissions) . ' permissions');
    }
}
