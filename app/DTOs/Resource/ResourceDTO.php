<?php

namespace App\DTOs\Resource;

use App\Enums\ResourceStatus;

/**
 * Typed transfer object for resource creation/update operations.
 */
final readonly class ResourceDTO
{
    public function __construct(
        public string $title,
        public ?string $description,
        public ResourceStatus $status,
        public ?string $updatedBy = null,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromRequest(array $validated, ?string $updatedBy = null): self
    {
        return new self(
            title:       $validated['title'],
            description: $validated['description'] ?? null,
            status:      ResourceStatus::from($validated['status'] ?? ResourceStatus::Draft->value),
            updatedBy:   $updatedBy,
        );
    }

    /**
     * Convert to array for model creation/update.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'title'       => $this->title,
            'description' => $this->description,
            'status'      => $this->status->value,
            'updated_by'  => $this->updatedBy,
        ], fn($value) => $value !== null);
    }
}
