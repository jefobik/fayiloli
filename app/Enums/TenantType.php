<?php

declare(strict_types=1);

namespace App\Enums;

enum TenantType: string
{
    // PHP 8.3 — typed class constants for static-analysis & IDE support
    const string GOVERNMENT_VALUE = 'government';
    const string MINISTRY_VALUE = 'ministry';
    const string SECRETARIAT_VALUE = 'secretariat';
    const string DEPARTMENT_VALUE = 'department';
    const string DIVISION_VALUE = 'division';
    const string AGENCY_VALUE = 'agency';
    const string UNIT_VALUE = 'unit';

    /**
     * Provide predefined tenant types for organizational hierarchy
     */

    case GOVERNMENT = self::GOVERNMENT_VALUE;
    case MINISTRY = self::MINISTRY_VALUE;
    case SECRETARIAT = self::SECRETARIAT_VALUE;
    case DEPARTMENT = self::DEPARTMENT_VALUE;
    case DIVISION = self::DIVISION_VALUE;
    case AGENCY = self::AGENCY_VALUE;
    case UNIT = self::UNIT_VALUE;

    public function label(): string
    {
        return match ($this) {
            self::GOVERNMENT => 'Government',
            self::AGENCY => 'Agency',
            self::DEPARTMENT => 'Department',
            self::SECRETARIAT => 'Secretariat',
            self::DIVISION => 'Division',
            self::MINISTRY => 'Ministry',
            self::UNIT => 'Unit',
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

