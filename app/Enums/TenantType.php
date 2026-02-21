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

    public function label(): string
    {
        return match ($this) {
            self::GOVERNMENT  => 'Government',
            self::AGENCY      => 'Agency',
            self::DEPARTMENT  => 'Department',
            self::SECRETARIAT => 'Secretariat',
            self::UNIT        => 'Unit',
        };
    }

    /** Filament / Spatie compatibility alias. */
    public function getLabel(): string
    {
        return $this->label();
    }

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

