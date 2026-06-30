<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * UserResource
 *
 * Transforms a User model into a consistent JSON structure.
 * Never exposes password, remember_token, or internal columns.
 *
 * The 'permissions' key is only included when the resource is loaded
 * with roles->permissions (eager-loading) to avoid N+1 in list views.
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'email'              => $this->email,
            'status'             => $this->status,
            'avatar'             => $this->avatar,
            'email_verified_at'  => $this->email_verified_at?->toIso8601String(),
            'created_at'         => $this->created_at?->toIso8601String(),
            'updated_at'         => $this->updated_at?->toIso8601String(),

            // Relationships — only include if loaded
            'roles' => $this->whenLoaded('roles', fn() =>
                $this->roles->map(fn($role) => [
                    'id'   => $role->id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                ])
            ),

            // Flat permission slugs — only included on profile/me endpoint
            'permissions' => $this->when(
                $this->relationLoaded('roles') && $this->roles->isNotEmpty(),
                fn() => $this->getAllPermissions()
            ),
        ];
    }
}
