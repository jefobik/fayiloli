<?php

declare(strict_types=1);

namespace App\Jobs\Tenancy;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Events\CreatingDatabase;
use Stancl\Tenancy\Events\DatabaseCreated;

/**
 * Idempotent replacement for Stancl\Tenancy\Jobs\CreateDatabase.
 *
 * Contract
 * ────────
 * • If the tenant's database already exists in PostgreSQL — skip creation and
 *   record that the DB was pre-existing so SeedTenantDatabase can skip seeding.
 * • If the database does not exist — create it and record it as freshly
 *   provisioned so SeedTenantDatabase seeds it normally.
 * • MigrateDatabase always runs after this job; Laravel's migration tracker
 *   makes it idempotent (already-applied migrations are skipped).
 *
 * Deterministic naming
 * ────────────────────
 * TenancyServiceProvider::boot() registers a custom DatabaseConfig generator:
 *   DatabaseConfig::generateDatabaseNamesUsing(fn($t) => 'tenant_' . $t->short_name)
 * This means the physical database name is derived from short_name (unique,
 * immutable after creation) rather than the tenant UUID.  Consequently:
 *   - migrate:fresh --seed  → same short_name → same DB name → pre-existing DB
 *                             detected → DB is reused, data is preserved
 *   - Accidental re-provision attempt → DB exists → silently skipped
 *
 * Thread-safety note
 * ──────────────────
 * $freshlyProvisioned is a static in-process map.  It is safe for synchronous
 * (shouldBeQueued=false) pipelines.  For queued pipelines, replace it with a
 * Redis/DB flag stored on the tenant's internal JSONB column.
 */
class CreateTenantDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * In-process registry of tenants provisioned in this request/command cycle.
     * true  → DB was created fresh  → SeedTenantDatabase should seed
     * false → DB already existed    → SeedTenantDatabase should skip
     *
     * @var array<string, bool>
     */
    public static array $freshlyProvisioned = [];

    protected TenantWithDatabase $tenant;

    public function __construct(TenantWithDatabase $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle(): void
    {
        // Honour stancl's built-in opt-out flag.
        if ($this->tenant->getInternal('create_database') === false) {
            static::$freshlyProvisioned[$this->tenant->getTenantKey()] = false;
            return;
        }

        // Persist credentials (db_name, etc.) to the tenant record.
        // For existing tenants this is a no-op — the stored name is returned as-is.
        // For new tenants, our deterministic generator (registered in
        // TenancyServiceProvider) sets "tenant_{short_name}".
        $this->tenant->database()->makeCredentials();

        $dbName  = $this->tenant->database()->getName();
        $manager = $this->tenant->database()->manager();

        if ($manager->databaseExists($dbName)) {
            // ── Database already exists — skip creation ────────────────────
            // Signal SeedTenantDatabase to skip seeding so we don't overwrite
            // existing users, roles, or EDMS data.
            static::$freshlyProvisioned[$this->tenant->getTenantKey()] = false;
            return;
        }

        // ── Fresh provisioning ─────────────────────────────────────────────
        event(new CreatingDatabase($this->tenant));
        $manager->createDatabase($this->tenant);
        event(new DatabaseCreated($this->tenant));

        static::$freshlyProvisioned[$this->tenant->getTenantKey()] = true;
    }
}
