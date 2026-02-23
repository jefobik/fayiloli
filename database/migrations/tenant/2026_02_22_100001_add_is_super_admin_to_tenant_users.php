<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add is_super_admin to the tenant users table.
 *
 * The shared App\Models\User model references is_super_admin in:
 *   - User::isSuperAdmin()
 *   - User::isAdminOrAbove()
 *   - AppServiceProvider::registerSuperAdminGate() (Gate::before callback)
 *   - User::$casts and User::$fillable
 *
 * The central users table already has this column (created in
 * 0001_01_01_000000_create_users_table.php and confirmed present in the
 * central DB schema).  However, the original tenant users migration did not
 * include it — leaving a hidden schema mismatch.
 *
 * While super-admin accounts are expected to exist only in the central
 * database, gating isSuperAdmin() on `$this->is_super_admin ?? false` masks
 * the missing column rather than declaring the intent.  Adding the column
 * with `default(false)` is the safe, explicit, and correct fix:
 *
 *   - No existing tenant user can gain super-admin privileges silently —
 *     all records default to false.
 *   - Future code can rely on the column existing in every users table
 *     without conditional guards.
 *   - avoids potential "column not found" errors if any query explicitly
 *     selects this column in a tenant context.
 *
 * The migration is idempotent: Schema::hasColumn() prevents a duplicate-
 * column error on installations that already applied a manual fix.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'is_super_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_super_admin')
                    ->default(false)
                    ->after('is_admin')
                    ->comment('Platform super-admin flag — must always be false in tenant context.');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'is_super_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_super_admin');
            });
        }
    }
};
