<?php

namespace App\Http\Resources;

use App\Enums\ResourceStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ResourceResource
 *
 * Transforms a Resource model. Conditionally includes:
 * - creator/updater/approver when eager-loaded
 * - status_label from the ResourceStatus enum
 * - signature_verified only when explicitly requested (expensive operation)
 */
class ResourceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = ResourceStatus::tryFrom($this->status);

        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'status'      => $this->status,
            'status_label' => $status?->label(),
            'status_color' => $status?->color(),
            'signature'   => $this->signature,
            'has_signature' => !is_null($this->signature),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
            'deleted_at'  => $this->deleted_at?->toIso8601String(),

            // Relationships — only when eager-loaded
            'created_by' => $this->whenLoaded('creator', fn() => [
                'id'    => $this->creator->id,
                'name'  => $this->creator->name,
                'email' => $this->creator->email,
            ]),
            'updated_by' => $this->whenLoaded('updater', fn() => [
                'id'    => $this->updater->id,
                'name'  => $this->updater->name,
                'email' => $this->updater->email,
            ]),
            'approved_by' => $this->whenLoaded('approver', fn() =>
                $this->approver ? [
                    'id'    => $this->approver->id,
                    'name'  => $this->approver->name,
                    'email' => $this->approver->email,
                ] : null
            ),
        ];
    }
}
