<?php

namespace App\Enums;

enum TenantType: string
{
    /**
     * Provide predefined tenant types for organizational hierarchy
     */

    case GOVERNMENT = 'government';
    case MINISTRY = 'ministry';
    case SECRETARIAT = 'secretariat';
    case DEPARTMENT = 'department';
    case DIVISION = 'division';
    case AGENCY = 'agency';
    case UNIT = 'unit';

    public function label(): string
    {
        return match ($this) {
            self::GOVERNMENT  => 'Government',
            self::AGENCY      => 'Agency',
            self::DEPARTMENT  => 'Department',
            self::SECRETARIAT => 'Secretariat',
            self::DIVISION    => 'Division',
            self::MINISTRY    => 'Ministry',
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
            self::MINISTRY => 'red',
            self::DIVISION => 'secondary',
            self::AGENCY => 'success',
            self::DEPARTMENT => 'info',
            self::SECRETARIAT => 'warning',
            self::UNIT => 'gray',
        };
    }
}

