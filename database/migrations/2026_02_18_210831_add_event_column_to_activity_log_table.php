<?php

use Illuminate\Database\Migrations\Migration;

/**
 * The `event` field is part of the MongoActivity document schema.
 *
 * MongoDB is schemaless — fields are added simply by including them in
 * the document on insert.  No ALTER TABLE equivalent is needed.
 */
return new class extends Migration
{
    public function up(): void
    {
        // No-op: MongoDB documents include the `event` field natively.
    }

    public function down(): void
    {
        // No-op.
    }
};
