<?php

declare(strict_types=1);

use App\Enums\TenantStatus;
use App\Enums\TenantType;
use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * DROP DATABASE cannot execute inside a PostgreSQL transaction block.
     * Setting this to false instructs Laravel to run this migration outside
     * a transaction so the tenant database cleanup in down() succeeds.
     */
    public $withinTransaction = false;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // your custom columns may go here
            $table->string('organization_name')->unique()->after('id');
            $table->string('short_name',30)->unique()->after('organization_name');
            $table->string('admin_email')->nullable()->after('short_name');
            $table->boolean('is_active')->default(true)->after('admin_email');
            $table->uuid('parent_id')->nullable()->comment('Parent tenant for multi-level hierarchy');
            $table->integer('level')->default(0)->comment('Hierarchy level (0=root, 1=child, etc.)');
            $table->string('hierarchy_path', 500)->nullable()->comment('Full path in hierarchy (e.g., uuid1/uuid2/uuid3)');
            $table->enum('tenant_type', array_column(TenantType::cases(), 'value'))->default(TenantType::AGENCY->value);
            $table->enum('status', array_column(TenantStatus::cases(), 'value'))->default(TenantStatus::PENDING->value);
            $table->text('notes')->nullable()->comment('Internal notes');
            $table->timestamps();
            $table->jsonb('data')->nullable();
            $table->jsonb('settings')->nullable()->comment('Tenant-specific settings');

            // Indexes
            $table->index('organization_name', 'indx_tenants_organization_name');
            $table->index('short_name', 'indx_tenants_short_name');
            $table->index('admin_email', 'indx_tenants_admin_email');
            $table->index('is_active', 'indx_tenants_is_active');
            $table->index('parent_id', 'indx_tenants_parent_id');
            $table->index('level', 'indx_tenants_level');
            $table->index('tenant_type', 'indx_tenants_tenant_type');
            $table->index('status', 'indx_tenants_status');

        });

        // ==================== FOREIGN KEY CONSTRAINTS ====================
        // Separate schema call to avoid circular dependencies
        Schema::table('tenants', function (Blueprint $table) {
            // Self-referencing foreign key for parent-child relationship
            $table->foreign('parent_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // Drop each tenant's PostgreSQL database before removing the registry table.
        // Without this, migrate:refresh accumulates orphaned tenant databases on every
        // run because the central registry is gone before databases can be identified.
        Tenant::all()->each(function (Tenant $tenant): void {
            try {
                $tenant->database()->manager()->deleteDatabase($tenant);
            } catch (\Throwable) {
                // Already dropped or connection unavailable — skip.
            }
        });

        Schema::dropIfExists('tenants');
    }
}
