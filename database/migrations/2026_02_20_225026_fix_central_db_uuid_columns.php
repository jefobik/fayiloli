<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fix central-database sessions.user_id column type.
 *
 * The original create_users_table migration created sessions.user_id as uuid,
 * but an earlier draft used foreignId() which produces bigint. This migration
 * ensures the central sessions table has the correct uuid type to match the
 * UUID primary key on the users table.
 *
 * EDMS tables (folders, documents, categories, tags, share_documents,
 * file_requests and all pivot tables) are NOT part of the central database.
 * They live exclusively in tenant databases. The companion migration
 * 2026_02_21_000001_drop_edms_tables_from_central.php removes any EDMS
 * tables that were mistakenly created in the central database by earlier
 * migration drafts.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Fix sessions.user_id: must be uuid to match users.id (UUID primary key).
        DB::statement('TRUNCATE TABLE sessions');
        DB::statement('ALTER TABLE sessions DROP COLUMN IF EXISTS user_id');
        DB::statement('ALTER TABLE sessions ADD COLUMN user_id uuid NULL');
        DB::statement('CREATE INDEX IF NOT EXISTS sessions_user_id_index ON sessions (user_id)');
    }

    public function down(): void
    {
        DB::statement('TRUNCATE TABLE sessions');
        DB::statement('ALTER TABLE sessions DROP COLUMN IF EXISTS user_id');
        DB::statement('ALTER TABLE sessions ADD COLUMN user_id bigint NULL');
        DB::statement('CREATE INDEX IF NOT EXISTS sessions_user_id_index ON sessions (user_id)');
    }
};
