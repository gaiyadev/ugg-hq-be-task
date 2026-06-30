<?php

namespace App\Enums;

enum ResourceStatus: string
{
    case Draft    = 'draft';
    case Pending  = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    /**
     * Human-readable label for UI display.
     */
    public function label(): string
    {
        return match($this) {
            self::Draft    => 'Draft',
            self::Pending  => 'Pending Review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    /**
     * Tailwind CSS badge color class for UI.
     */
    public function color(): string
    {
        return match($this) {
            self::Draft    => 'gray',
            self::Pending  => 'yellow',
            self::Approved => 'green',
            self::Rejected => 'red',
        };
    }

    /**
     * Valid transitions from the current status.
     * Enforces the state machine in ResourceService.
     *
     * @return array<ResourceStatus>
     */
    public function allowedTransitions(): array
    {
        return match($this) {
            self::Draft    => [self::Pending],
            self::Pending  => [self::Approved, self::Rejected, self::Draft],
            self::Approved => [],
            self::Rejected => [self::Draft],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }
}
