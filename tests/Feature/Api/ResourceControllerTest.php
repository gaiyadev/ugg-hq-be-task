<?php

namespace Tests\Feature\Api;

use App\Models\Resource;
use App\Models\Role;
use App\Models\User;
use App\Enums\ResourceStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ResourceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed'); // Seed roles and permissions
    }

    private function createAuthUser(string $roleSlug): User
    {
        $user = User::factory()->create();
        $role = Role::where('slug', $roleSlug)->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }
        Sanctum::actingAs($user);
        return $user;
    }

    public function test_regular_user_cannot_approve_resource()
    {
        $user = $this->createAuthUser('user'); // Regular user, no approve_resources permission

        $resource = Resource::create([
            'title' => 'Test',
            'status' => ResourceStatus::Pending->value,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this->postJson("/api/resources/{$resource->id}/approve");

        // Forbidden
        $response->assertStatus(403);
    }

    public function test_admin_can_approve_pending_resource()
    {
        $admin = $this->createAuthUser('admin'); // Admin has approve_resources

        $resource = Resource::create([
            'title' => 'Test',
            'status' => ResourceStatus::Pending->value,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $response = $this->postJson("/api/resources/{$resource->id}/approve");

        $response->assertStatus(200);
        $response->assertJsonPath('data.status', 'approved');
        
        $this->assertDatabaseHas('resources', [
            'id' => $resource->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);
    }

    public function test_creator_can_submit_draft_resource()
    {
        $user = $this->createAuthUser('user');

        $resource = Resource::create([
            'title' => 'My Draft',
            'status' => ResourceStatus::Draft->value,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this->postJson("/api/resources/{$resource->id}/submit");

        $response->assertStatus(200);
        $response->assertJsonPath('data.status', 'pending');
    }
}
