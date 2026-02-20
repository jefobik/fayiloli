<?php

namespace App\Enums;

enum TenantStatus: string
{
    /**
     * Provide predefined tenant statuses for tenant lifecycle management
     */

    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case PENDING = 'pending';

    /**
     * Get the human-readable label for the tenant status
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::SUSPENDED => 'Suspended',
            self::PENDING => 'Pending Activation',
        };
    }

    /**
     * Get the color for Filament badge display
     */
    public function getColor(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'gray',
            self::SUSPENDED => 'danger',
            self::PENDING => 'warning',
        };
    }
}
