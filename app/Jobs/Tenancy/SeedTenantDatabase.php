<?php

declare(strict_types=1);

namespace App\Jobs\Tenancy;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Jobs\SeedDatabase;

/**
 * Conditional wrapper around Stancl\Tenancy\Jobs\SeedDatabase.
 *
 * Seeding is an expensive, destructive operation on an already-populated
 * database — re-running it would duplicate roles, permissions, and sample
 * users.  This job consults CreateTenantDatabase::$freshlyProvisioned to
 * determine whether the database was created in this request/command cycle.
 *
 * • Fresh database  → delegates to stancl's SeedDatabase (runs TenantDatabaseSeeder)
 * • Pre-existing DB → returns immediately without touching tenant data
 *
 * Default behaviour when the flag is absent (e.g. SeedTenantDatabase is
 * dispatched independently without CreateTenantDatabase having run first)
 * is to SEED — ensuring tenant databases are never left un-seeded by accident.
 */
class SeedTenantDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected TenantWithDatabase $tenant;

    public function __construct(TenantWithDatabase $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle(): void
    {
        $tenantKey = $this->tenant->getTenantKey();

        // If CreateTenantDatabase explicitly flagged this DB as pre-existing,
        // skip seeding to preserve existing data.
        if (isset(CreateTenantDatabase::$freshlyProvisioned[$tenantKey])
            && CreateTenantDatabase::$freshlyProvisioned[$tenantKey] === false
        ) {
            return;
        }

        // Delegate to stancl's standard seeder (calls `artisan tenants:seed`).
        (new SeedDatabase($this->tenant))->handle();
    }
}
