<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed known system users + faker-generated test users.
     * System users have fixed credentials for dev access.
     */
    public function run(): void
    {
        // ─── System Users (fixed credentials) ────────────────────────────
        $systemUsers = [
            [
                'name'     => 'Super Admin',
                'email'    => 'superadmin@ugg-hq.com',
                'password' => Hash::make('SuperAdmin@123!'),
                'status'   => 'active',
                'role'     => 'super-admin',
            ],
            [
                'name'     => 'Admin User',
                'email'    => 'admin@ugg-hq.com',
                'password' => Hash::make('Admin@123!'),
                'status'   => 'active',
                'role'     => 'admin',
            ],
            [
                'name'     => 'Manager User',
                'email'    => 'manager@ugg-hq.com',
                'password' => Hash::make('Manager@123!'),
                'status'   => 'active',
                'role'     => 'manager',
            ],
            [
                'name'     => 'Viewer User',
                'email'    => 'viewer@ugg-hq.com',
                'password' => Hash::make('Viewer@123!'),
                'status'   => 'active',
                'role'     => 'viewer',
            ],
            [
                'name'     => 'Regular User',
                'email'    => 'user@ugg-hq.com',
                'password' => Hash::make('User@123!'),
                'status'   => 'active',
                'role'     => 'user',
            ],
        ];

        foreach ($systemUsers as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, ['email_verified_at' => now()])
            );

            // Assign role via pivot
            $roleModel = Role::where('slug', $role)->first();
            if ($roleModel) {
                $user->roles()->syncWithoutDetaching([$roleModel->id]);
            }
        }

        // ─── Faker Users (for testing / dev only) ────────────────────────
        if (app()->environment('local', 'testing')) {
            $userRole = Role::where('slug', 'user')->first();

            User::factory(20)->create()->each(function (User $user) use ($userRole) {
                if ($userRole) {
                    $user->roles()->syncWithoutDetaching([$userRole->id]);
                }
            });

            $this->command->info('✅ Generated 20 faker users with "user" role');
        }

        $this->command->info('✅ System users seeded: ' . count($systemUsers) . ' users');
    }
}
