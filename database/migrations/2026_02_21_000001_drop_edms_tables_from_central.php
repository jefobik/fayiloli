<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Remove EDMS application tables from the central database.
 *
 * The central database is authoritative only for multi-tenancy infrastructure:
 *   tenants, domains, users (super-admin), sessions, cache, jobs, and
 *   Spatie permission tables for central RBAC.
 *
 * All EDMS tables (folders, documents, categories, tags, share_documents,
 * file_requests, notifications and their pivot/junction tables) belong
 * exclusively in each tenant's own database.  This migration drops any of
 * those tables that were mistakenly created in the central database by earlier
 * migration drafts, without affecting the tenant databases.
 *
 * dropIfExists() makes every statement idempotent — safe to run on a fresh
 * central database that never had these tables.
 */
return new class extends Migration
{
    /**
     * Tables to be removed from the central database.
     * Order respects FK dependency (junction tables first).
     */
    private array $edmsTables = [
        // Junction / pivot tables (no dependents)
        'category_tag',
        'category_folder',
        'document_tag',
        'folder_tag',
        // Leaf EDMS tables
        'file_requests',
        'share_documents',
        'notifications',
        // Core EDMS tables (depended on by pivots above)
        'documents',
        'tags',
        'folders',
        'categories',
    ];

    public function up(): void
    {
        // Disable FK checks so we can drop in one pass without strict ordering.
        Schema::disableForeignKeyConstraints();

        foreach ($this->edmsTables as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // These tables must NOT be recreated here — they are tenant-only.
        // To restore them on a tenant database, run:  php artisan tenants:migrate
    }
};
