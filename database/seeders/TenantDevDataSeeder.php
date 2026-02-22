<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Development-only content fixtures for tenant databases.
 *
 * CONTRACT — this seeder is development-only:
 *   • Throws RuntimeException if executed in the production environment.
 *   • Runs inside the current tenant's isolated database connection.
 *   • Called by TenantDatabaseSeeder when APP_ENV=local|testing.
 *
 * What it seeds (dev fixtures only):
 *   UserSeeder        — 13 predictable dev users (superadmin, admin1-2, user1-10)
 *   CategorySeeder    — 121 document categories with nested tags
 *   FolderSeeder      — 4 root registry folders
 *   TagSeeder         — 51 EDMS classification tags
 *   DocumentSeeder    — up to 30 documents from public/documents
 *
 * In production, tenant workspaces start clean.  Real users are invited by the
 * tenant admin and real documents are uploaded through the EDMS UI.  Fake
 * fixture data must never appear in a live tenant workspace.
 */
class TenantDevDataSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException(
                'TenantDevDataSeeder must not run in production. '
                . 'Tenant workspaces are populated by users through the EDMS UI.'
            );
        }

        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            FolderSeeder::class,
            TagSeeder::class,
            DocumentSeeder::class,
        ]);
    }
}
