<?php

namespace App\Http\Resources;

use App\Enums\AuditAction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * AuditLogResource
 *
 * Transforms an AuditLog model.
 * Includes enum-derived action_label for human-readable display.
 */
class AuditLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $action = AuditAction::tryFrom($this->action);

        return [
            'id'           => $this->id,
            'action'       => $this->action,
            'action_label' => $action?->label() ?? $this->action,
            'entity_type'  => $this->entity_type,
            'entity_id'    => $this->entity_id,
            'description'  => $this->description,
            'metadata'     => $this->metadata,
            'ip_address'   => $this->ip_address,
            'user_agent'   => $this->user_agent,
            'created_at'   => $this->created_at?->toIso8601String(),

            'user' => $this->whenLoaded('user', fn() =>
                $this->user ? [
                    'id'    => $this->user->id,
                    'name'  => $this->user->name,
                    'email' => $this->user->email,
                ] : null
            ),
        ];
    }
}
