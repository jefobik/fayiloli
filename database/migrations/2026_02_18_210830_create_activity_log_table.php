<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Activity log storage → MongoDB (schemaless).
 *
 * The `activity_log` collection lives in the `edms_nosql` MongoDB database
 * (connection: mongodb).  MongoDB creates collections automatically on the
 * first document insert, so no DDL is needed here.
 *
 * Indexes are managed via App\Console\Commands\EnsureMongoIndexes or
 * directly through the MongoDB shell / Atlas UI.
 */
return new class extends Migration
{
    public function up(): void
    {
        // No-op: MongoDB collection created automatically on first write.
    }

    public function down(): void
    {
        // No-op: dropping the collection is handled outside of migrations
        // to avoid accidental data loss.
    }
};
