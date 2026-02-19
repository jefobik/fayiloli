<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extends the base stancl/tenancy `tenants` table with EDMS-specific columns.
 *
 * The package ships with (id, data jsonb, timestamps).  We promote the fields
 * that are critical for central administration out of the JSON blob into proper
 * typed columns so they can be indexed, validated, and used in queries without
 * JSON path operators.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('organization_name')->nullable()->after('id');
            $table->string('admin_email')->nullable()->after('organization_name');
            $table->enum('plan', ['starter', 'business', 'enterprise'])->default('starter')->after('admin_email');
            $table->boolean('is_active')->default(true)->after('plan');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['organization_name', 'admin_email', 'plan', 'is_active']);
        });
    }
};
