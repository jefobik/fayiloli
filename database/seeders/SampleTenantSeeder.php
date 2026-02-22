<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TenantModule;
use App\Enums\TenantStatus;
use App\Enums\TenantType;
use App\Models\Tenant;
use App\Services\SubdomainGenerator;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Provisions representative sample tenants for local development.
 *
 * CONTRACT — this seeder is development-only:
 *   • Throws RuntimeException if executed in the production environment.
 *   • Each provisioned tenant fires the full stancl/tenancy event pipeline:
 *       TenantCreated → CreateDatabase → MigrateDatabase → SeedDatabase
 *     which in turn executes TenantDatabaseSeeder inside the tenant's own
 *     isolated PostgreSQL database.
 *
 * Invocation:
 *   Automatically called by DatabaseSeeder when APP_ENV=local|testing.
 *   Can also be invoked standalone for a reset of sample tenants only:
 *     php artisan db:seed --class=SampleTenantSeeder
 *
 * /etc/hosts requirements (local only):
 *   127.0.0.1  finance.localhost
 *   127.0.0.1  hra.localhost
 *
 * In production, tenants are provisioned exclusively through the admin portal
 * via TenantController::store() — never through a seeder.
 */
class SampleTenantSeeder extends Seeder
{
    public function __construct(private readonly SubdomainGenerator $subdomainGenerator) {}

    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException(
                'SampleTenantSeeder must not run in production. '
                . 'Provision tenants through the admin portal (TenantController::store()).'
            );
        }

        $this->provisionAll([
            [
                'organization_name' => 'Office of the Honourable Minister FCTA',
                'short_name'        => 'ohm',
                'admin_email'       => 'registry@ohm.fcta.gov.ng',
                'tenant_type'       => TenantType::DEPARTMENT,
            ],
            [
                'organization_name' => 'Office of the Honourable Minister of State FCTA',
                'short_name'        => 'ohms',
                'admin_email'       => 'registry@ohms.fcta.gov.ng',
                'tenant_type'       => TenantType::DEPARTMENT,
            ],
            [
                'organization_name' => 'Permanent Secretary Common Services Secretariat FCTA',
                'short_name'        => 'pscss',
                'admin_email'       => 'registry@pscss.fcta.gov.ng',
                'tenant_type'       => TenantType::SECRETARIAT,
            ],
            [
                'organization_name' => 'Head of Service FCTA',
                'short_name'        => 'hos',
                'admin_email'       => 'registry@hos.fcta.gov.ng',
                'tenant_type'       => TenantType::DEPARTMENT,
            ],
            [
                'organization_name' => 'Procurement Department of FCTA',
                'short_name'        => 'procurement',
                'admin_email'       => 'registry@procurement.fcta.gov.ng',
                'tenant_type'       => TenantType::SECRETARIAT,
            ],

            [
                'organization_name' => 'Treasury and Budget Secretariat',
                'short_name'        => 'treasury',
                'admin_email'       => 'registry@treasury.fcta.gov.ng',
                'tenant_type'       => TenantType::SECRETARIAT,
            ],
        ]);
    }

    /** @param array<int, array<string, mixed>> $tenants */
    private function provisionAll(array $tenants): void
    {
        foreach ($tenants as $data) {
            if (Tenant::where('short_name', $data['short_name'])->exists()) {
                $this->command?->line("  Tenant [{$data['short_name']}] already exists — skipped.");
                continue;
            }

            $this->command?->line("  Provisioning tenant [{$data['short_name']}]…");

            // Tenant::create() fires TenantCreated → CreateDatabase
            // → MigrateDatabase → SeedDatabase(TenantDatabaseSeeder).
            $tenant = Tenant::create([
                'organization_name' => $data['organization_name'],
                'short_name'        => $data['short_name'],
                'admin_email'       => $data['admin_email'],
                'tenant_type'       => $data['tenant_type'],
                'status'            => TenantStatus::PENDING,
                'settings'          => ['modules' => TenantModule::defaults()],
            ]);

            // Attach primary domain using the same generator as TenantController::store().
            $fqdn = $this->subdomainGenerator->generate($data['short_name']);
            $tenant->domains()->create(['domain' => $fqdn]);

            // Activate immediately — provisioning is synchronous in dev.
            $tenant->transitionStatus(TenantStatus::ACTIVE);

            $this->command?->info("  Tenant [{$data['short_name']}] provisioned at {$fqdn} ✓");
        }
    }
}
