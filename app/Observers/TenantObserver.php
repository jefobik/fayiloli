<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\TenantStatus;
use App\Models\Tenant;

/**
 * TenantObserver — reactive side-effects for tenant lifecycle events.
 *
 * Primary responsibility: keep `is_active` in sync with `status` so that
 * the two fields never drift.  `is_active` must never be written directly
 * by callers — it is wholly derived from `status`.
 */
class TenantObserver
{
    /**
     * Before saving, derive is_active from status and ensure status is
     * always set (default to PENDING for brand-new tenants that don't
     * have an explicit status yet).
     */
    public function saving(Tenant $tenant): void
    {
        // Default unset status to PENDING on first save.
        if (! $tenant->status) {
            $tenant->status = TenantStatus::PENDING;
        }

        // is_active is always derived — it must never drift from status.
        $tenant->is_active = ($tenant->status === TenantStatus::ACTIVE);
    }

    /**
     * After a status change is persisted, flush the Spatie permission cache
     * so that any permission checks re-evaluate against the new state.
     */
    public function updated(Tenant $tenant): void
    {
        if ($tenant->wasChanged('status')) {
            app('cache')
                ->store(
                    config('permission.cache.store') !== 'default'
                        ? config('permission.cache.store')
                        : null
                )
                ->forget(config('permission.cache.key'));
        }
    }
}
