<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix tenant permission tables and notifications.notifiable_id column.
 *
 * PERMISSION TABLES
 * -----------------
 * The original tenant permission migration used $table->id() (BigInteger
 * auto-increment) for permissions.id, roles.id and unsignedBigInteger for
 * the model_morph_key column in the pivot tables.  Because the tenant users
 * table uses HasUuids (UUID primary keys), assigning a role to a user writes
 * a UUID string into model_morph_key — which PostgreSQL cannot store in a
 * bigint column.  This migration rebuilds all five Spatie permission tables
 * with UUID columns, matching the central database schema and the tenant
 * users table.
 *
 * Existing role/permission assignments are cleared (dev environment only).
 * Re-run RolesPermissionsSeeder after this migration to restore them.
 *
 * NOTIFICATIONS
 * -------------
 * notifications.notifiable_id was created as bigInteger but must be uuid to
 * store user UUIDs from the notifiable() morph relationship.
 */
return new class extends Migration
{
    public function up(): void
    {
        $tableNames  = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole   = $columnNames['role_pivot_key']       ?? 'role_id';
        $pivotPerm   = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $teams       = config('permission.teams');

        // ── 1. Rebuild Spatie permission tables with UUID IDs ────────────────
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);

        Schema::create($tableNames['permissions'], static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['roles'], static function (Blueprint $table) use ($teams, $columnNames) {
            $table->uuid('id')->primary();
            if ($teams || config('permission.testing')) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $teams
                ? $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name'])
                : $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['model_has_permissions'], static function (Blueprint $table) use (
            $tableNames, $columnNames, $pivotPerm, $teams
        ) {
            $table->uuid($pivotPerm);
            $table->string('model_type');
            $table->uuid($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $table->foreign($pivotPerm)->references('id')->on($tableNames['permissions'])->cascadeOnDelete();
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');
                $table->primary(
                    [$columnNames['team_foreign_key'], $pivotPerm, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary'
                );
            } else {
                $table->primary(
                    [$pivotPerm, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary'
                );
            }
        });

        Schema::create($tableNames['model_has_roles'], static function (Blueprint $table) use (
            $tableNames, $columnNames, $pivotRole, $teams
        ) {
            $table->uuid($pivotRole);
            $table->string('model_type');
            $table->uuid($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');
            $table->foreign($pivotRole)->references('id')->on($tableNames['roles'])->cascadeOnDelete();
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');
                $table->primary(
                    [$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
            } else {
                $table->primary(
                    [$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
            }
        });

        Schema::create($tableNames['role_has_permissions'], static function (Blueprint $table) use (
            $tableNames, $pivotRole, $pivotPerm
        ) {
            $table->uuid($pivotPerm);
            $table->uuid($pivotRole);
            $table->foreign($pivotPerm)->references('id')->on($tableNames['permissions'])->cascadeOnDelete();
            $table->foreign($pivotRole)->references('id')->on($tableNames['roles'])->cascadeOnDelete();
            $table->primary([$pivotPerm, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        Schema::enableForeignKeyConstraints();

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));

        // ── 2. Fix notifications.notifiable_id: bigint → uuid ───────────────
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', static function (Blueprint $table) {
                $table->dropIndex(['notifiable_type', 'notifiable_id']);
            });

            Schema::table('notifications', static function (Blueprint $table) {
                $table->dropColumn('notifiable_id');
            });

            Schema::table('notifications', static function (Blueprint $table) {
                $table->uuid('notifiable_id')->nullable()->after('notifiable_type');
                $table->index(['notifiable_type', 'notifiable_id']);
            });
        }
    }

    public function down(): void
    {
        // Rebuilding with bigint IDs is intentionally not supported.
        // The BigInteger schema was a bug — not a valid prior state.
    }
};
