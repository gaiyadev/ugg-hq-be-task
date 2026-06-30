<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * RoleResource
 *
 * Transforms a Role model into a structured JSON response.
 * user_count only appears when loaded via withCount('users').
 */
class RoleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'is_system'   => $this->is_system,
            'user_count'  => $this->when(isset($this->users_count), $this->users_count),
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),

            'permissions' => $this->whenLoaded('permissions', fn() =>
                $this->permissions->map(fn($permission) => [
                    'id'    => $permission->id,
                    'name'  => $permission->name,
                    'slug'  => $permission->slug,
                    'group' => $permission->group,
                ])
            ),
        ];
    }
}
