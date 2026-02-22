<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add tracking columns to the central users table.
 *
 * The tenant users table (database/migrations/tenant/0001_01_01_000000_create_users_table.php)
 * was created with full tracking from the start: failed_login_attempts, last_login_at,
 * locked_at, and softDeletes (deleted_at).
 *
 * The central users table was originally created without these columns.  This
 * additive, idempotent migration closes the gap so that:
 *
 *  1. User::SoftDeletes (added 2026-02-22) applies correctly on both domains —
 *     without deleted_at in the central table every auth query would throw
 *     "column deleted_at does not exist".
 *
 *  2. LoginController::authenticated() and trackFailedAttempt() can update
 *     last_login_at, failed_login_attempts, and locked_at on the central
 *     admin user record without exploding.
 *
 * All additions are guarded with Schema::hasColumn() so the migration is safe
 * to run on installations that already applied manual schema changes.
 */
return new class extends Migration
{
    /**
     * This migration always runs against the central database connection,
     * never against a tenant connection.
     */
    public function getConnection(): string
    {
        return config('tenancy.database.central_connection', 'central');
    }

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // ── Soft delete ───────────────────────────────────────────────────
            // SoftDeletes adds a global WHERE deleted_at IS NULL scope to every
            // Eloquent query on the User model.  Without this column on the
            // central table, login and all central-domain user queries would
            // throw a PostgreSQL "column deleted_at does not exist" error.
            if (! Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }

            // ── Failed-login tracking ─────────────────────────────────────────
            // Incremented by LoginController::trackFailedAttempt(); reset to 0
            // on every successful login.  When this reaches
            // LoginController::MAX_ATTEMPTS_BEFORE_LOCK (5) the account is
            // auto-locked and an administrator must unlock it via the
            // UserManagementController.
            if (! Schema::hasColumn('users', 'failed_login_attempts')) {
                $table->integer('failed_login_attempts')->default(0)->after('deleted_at');
            }

            // ── Last successful login ─────────────────────────────────────────
            // Updated by LoginController::authenticated() on every successful
            // authentication.  Surfaced in the User Management panel so
            // administrators can identify dormant accounts.
            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('failed_login_attempts');
            }

            // ── Account lock timestamp ────────────────────────────────────────
            // Set together with is_locked = true when an account is auto-locked.
            // Administrators can view this to understand when a lockout occurred.
            if (! Schema::hasColumn('users', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('last_login_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = array_filter(
                ['deleted_at', 'failed_login_attempts', 'last_login_at', 'locked_at'],
                fn (string $col) => Schema::hasColumn('users', $col)
            );

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
