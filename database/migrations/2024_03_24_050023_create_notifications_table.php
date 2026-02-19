<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Notification storage → MongoDB (schemaless).
 *
 * The App\Models\Notification model was migrated from PostgreSQL to MongoDB
 * as part of the hybrid database architecture.  Notifications are stored in
 * the `edms_nosql` MongoDB database under the `notifications` collection.
 *
 * MongoDB creates the collection automatically on the first document insert,
 * so no DDL is required here.  The original PostgreSQL `notifications` table
 * definition is intentionally removed to avoid confusion with the active
 * MongoDB-backed collection.
 */
return new class extends Migration
{
    public function up(): void
    {
        // No-op: Notification model uses MongoDB (connection: mongodb).
        // Collection is created automatically on first write.
    }

    public function down(): void
    {
        // No-op.
    }
};
