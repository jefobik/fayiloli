<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds short_name to the tenants table.
 *
 * short_name is the admin-supplied abbreviation (e.g. 'fmof', 'nhra') that
 * SubdomainGenerator uses as the slug seed.  It is distinct from
 * organization_name, which is the full official name used for display only.
 *
 * Added as nullable so existing rows (if any) are not broken.
 * NOT NULL is enforced at the application layer via StoreTenantRequest.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Guard: the base create_tenants_table migration now includes short_name,
        // so on a fresh install the column already exists before this migration runs.
        // The additive migration is still needed for databases provisioned before
        // short_name was added to the base migration.
        if (Schema::hasColumn('tenants', 'short_name')) {
            return;
        }

        Schema::table('tenants', function (Blueprint $table) {
            $table->string('short_name', 30)
                  ->nullable()
                  ->unique('uniq_tenants_short_name');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('short_name');
        });
    }
};
