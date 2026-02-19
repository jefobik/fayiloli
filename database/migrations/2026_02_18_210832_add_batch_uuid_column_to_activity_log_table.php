<?php

use Illuminate\Database\Migrations\Migration;

/**
 * The `batch_uuid` field is part of the MongoActivity document schema.
 *
 * MongoDB is schemaless — the field is present in all new documents
 * written by MongoActivity::$fillable.  No column addition is needed.
 */
return new class extends Migration
{
    public function up(): void
    {
        // No-op: MongoDB documents include the `batch_uuid` field natively.
    }

    public function down(): void
    {
        // No-op.
    }
};
