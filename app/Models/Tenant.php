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
            'id', 'organization_name', 'admin_email', 'plan', 'is_active',
            'parent_uuid', 'level', 'hierarchy_path',
            'tenant_type', 'status', 'settings', 'notes',
        ];
    }

    protected $fillable = [
        'id', 'organization_name', 'admin_email', 'plan', 'is_active',
        'parent_uuid', 'level', 'hierarchy_path',
        'tenant_type', 'status', 'settings', 'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings'  => 'array',
    ];

    public function getPlanBadgeAttribute(): string
    {
        return match($this->plan) {
            'government'  => 'bg-red-100 text-red-800',
            'secretariat' => 'bg-purple-100 text-purple-800',
            'agency'      => 'bg-blue-100 text-blue-800',
            'department'  => 'bg-green-100 text-green-800',
            'unit'        => 'bg-yellow-100 text-yellow-800',
            default       => 'bg-gray-100 text-gray-700',
        };
    }
}
