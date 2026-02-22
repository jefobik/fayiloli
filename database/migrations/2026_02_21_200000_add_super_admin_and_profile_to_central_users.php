<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive migration for existing central databases.
 *
 * Adds the is_super_admin flag plus any user-profile columns that were
 * missing from the original central users migration.  All additions use
 * "if not exists" guards so the migration is idempotent and safe to run
 * on both old and new installations.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'user_name')) {
                $table->string('user_name')->nullable()->unique()->after('name');
            }
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'is_super_admin')) {
                // Grant global Gate bypass — only set on the platform super-admin account.
                $table->boolean('is_super_admin')->default(false)->after('remember_token');
            }
            if (! Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false)->after('is_super_admin');
            }
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_admin');
            }
            if (! Schema::hasColumn('users', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('is_active');
            }
            if (! Schema::hasColumn('users', 'is_2fa_enabled')) {
                $table->boolean('is_2fa_enabled')->default(false)->after('is_locked');
            }
        });
    }

    public function down(): void
    {
        $columns = ['user_name', 'phone', 'is_super_admin', 'is_admin', 'is_active', 'is_locked', 'is_2fa_enabled'];
        $existing = array_filter($columns, fn($col) => Schema::hasColumn('users', $col));

        if ($existing) {
            Schema::table('users', function (Blueprint $table) use ($existing) {
                $table->dropColumn(array_values($existing));
            });
        }
    }
};
