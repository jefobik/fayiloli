<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TenantModule;
use App\Enums\TenantStatus;
use App\Enums\TenantType;
use App\Observers\TenantObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

#[ObservedBy(TenantObserver::class)]
class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * Columns stored directly in the tenants table (not in the JSONB data column).
     *
     * IMPORTANT: do NOT list tenancy_db_name / tenancy_db_username /
     * tenancy_db_password here — stancl manages those internally via
     * setInternal/getInternal.  Including them here causes "no password
     * supplied" errors on every tenant DB connection.
     */
    public static function getCustomColumns(): array
    {
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
        'is_active'   => 'boolean',
        'settings'    => 'array',
        'status'      => TenantStatus::class,
        'tenant_type' => TenantType::class,
    ];

    // ── Module access helpers ─────────────────────────────────────────────────

    /**
     * Check whether a given module is enabled for this tenant.
     *
     * Falls back to TenantModule::defaults() when `settings.modules` has
     * never been explicitly set (e.g. tenants provisioned before modules
     * were introduced).
     */
    public function hasModule(TenantModule $module): bool
    {
        $enabled = $this->settings['modules'] ?? TenantModule::defaults();

        return in_array($module->value, $enabled, strict: true);
    }

    /**
     * Return the list of enabled module string values.
     *
     * @return list<string>
     */
    public function enabledModules(): array
    {
        return $this->settings['modules'] ?? TenantModule::defaults();
    }

    // ── Status convenience helpers ────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === TenantStatus::ACTIVE;
    }

    public function isPending(): bool
    {
        return $this->status === TenantStatus::PENDING;
    }

    public function isSuspended(): bool
    {
        return $this->status === TenantStatus::SUSPENDED;
    }

    public function isInactive(): bool
    {
        return $this->status === TenantStatus::INACTIVE;
    }

    // ── State machine transition ──────────────────────────────────────────────

    /**
     * Apply a status transition, validating it against the state machine.
     *
     * Throws \InvalidArgumentException when the transition is not permitted.
     * On success the model is saved and `is_active` is synced automatically
     * by TenantObserver::updating().
     *
     * @throws \InvalidArgumentException
     */
    public function transitionStatus(TenantStatus $newStatus, ?string $reason = null): void
    {
        $current = $this->status ?? TenantStatus::PENDING;

        if (! $current->canTransitionTo($newStatus)) {
            throw new \InvalidArgumentException(
                "Cannot transition tenant from [{$current->label()}] to [{$newStatus->label()}]."
            );
        }

        $this->status = $newStatus;

        if ($reason) {
            $this->notes = $reason;
        }

        $this->save();
    }

    // ── Computed badge attributes ─────────────────────────────────────────────

    /**
     * Bootstrap badge class derived from the TenantStatus enum.
     * Replaces the old hard-coded string match in the model.
     */
    public function getStatusBadgeAttribute(): string
    {
        return $this->status?->badgeClass() ?? 'bg-secondary';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status?->label() ?? '—';
    }

    public function getStatusIconAttribute(): string
    {
        return $this->status?->icon() ?? 'circle';
    }

    public function getPlanBadgeAttribute(): string
    {
        return match ($this->tenant_type?->value ?? $this->tenant_type) {
            'government'  => 'bg-danger',
            'secretariat' => 'bg-primary',
            'agency'      => 'bg-info text-dark',
            'department'  => 'bg-success',
            'unit'        => 'bg-warning text-dark',
            default       => 'bg-secondary',
        };
    }

    public function getPlanLabelAttribute(): string
    {
        return $this->tenant_type?->label()
            ?? ucfirst((string) ($this->tenant_type ?? '—'));
    }
}
