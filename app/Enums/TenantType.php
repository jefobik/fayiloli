<?php

namespace App\Enums;

enum TenantType: string
{
    /**
     * Provide predefined tenant types for organizational hierarchy
     */

    case GOVERNMENT = 'government';
    case AGENCY = 'agency';
    case DEPARTMENT = 'department';
    case SECRETARIAT = 'secretariat';
    case UNIT = 'unit';

    /**
     * Get the human-readable label for the tenant type
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::GOVERNMENT => 'Government',
            self::AGENCY => 'Agency',
            self::DEPARTMENT => 'Department',
            self::SECRETARIAT => 'Secretariat',
            self::UNIT => 'Unit',
        };
    }

    /**
     * Get the color for Filament badge display
     */
    public function getColor(): string
    {
        return match ($this) {
            self::GOVERNMENT => 'primary',
            self::AGENCY => 'success',
            self::DEPARTMENT => 'info',
            self::SECRETARIAT => 'warning',
            self::UNIT => 'gray',
        };
    }
}

