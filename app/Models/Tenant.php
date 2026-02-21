<?php

namespace App\Models;

use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    public static function getCustomColumns(): array
    {
        // IMPORTANT: do NOT list tenancy_db_name / tenancy_db_username /
        // tenancy_db_password here.  stancl/tenancy stores those internally
        // via setInternal/getInternal (prefixed under internalPrefix()).
        // If they appear here, DatabaseConfig::tenantConfig() will find null
        // values and overwrite the template connection credentials — causing
        // "no password supplied" on every tenant DB connection.
        return [
            'id', 'organization_name', 'admin_email', 'is_active',
            'parent_uuid', 'level', 'hierarchy_path',
            'tenant_type', 'status', 'settings', 'notes',
        ];
    }

    protected $fillable = [
        'id', 'organization_name', 'admin_email', 'is_active',
        'parent_uuid', 'level', 'hierarchy_path',
        'tenant_type', 'status', 'settings', 'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings'  => 'array',
    ];

    public function getPlanBadgeAttribute(): string
    {
        return match($this->tenant_type ?? $this->plan) {
            'government'  => 'bg-danger',
            'secretariat' => 'bg-primary',
            'agency'      => 'bg-info text-dark',
            'department'  => 'bg-success',
            'unit'        => 'bg-warning text-dark',
            default       => 'bg-secondary',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'active'    => 'bg-success',
            'pending'   => 'bg-warning text-dark',
            'suspended' => 'bg-danger',
            'archived'  => 'bg-secondary',
            default     => 'bg-secondary',
        };
    }
}
