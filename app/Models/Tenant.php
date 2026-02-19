<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Contracts\Tenancy\Tenant as TenantContract;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantContract
{
    use HasDatabase, HasDomains;

    public static function getCustomColumns(): array
    {
        return ['id', 'organization_name', 'admin_email', 'plan', 'is_active'];
    }

    protected $fillable = ['id', 'organization_name', 'admin_email', 'plan', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getPlanBadgeAttribute(): string
    {
        return match($this->plan) {
            'enterprise' => 'bg-purple-100 text-purple-800',
            'business'   => 'bg-blue-100 text-blue-800',
            'starter'    => 'bg-green-100 text-green-800',
            default      => 'bg-gray-100 text-gray-700',
        };
    }
}
