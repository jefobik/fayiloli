<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * All many-to-many pivot tables that carry no extra payload columns.
 *
 * Bundled into a single migration to keep ordering crystal-clear and avoid
 * any FK resolution issues between separate migration files.
 */
return new class extends Migration
{
    public function up(): void
    {
        // folder <-> tag
        Schema::create('folder_tag', function (Blueprint $table) {
            $table->foreignUuid('folder_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('tag_id')->constrained()->onDelete('cascade');
            $table->primary(['folder_id', 'tag_id']);
        });

        // document <-> tag
        Schema::create('document_tag', function (Blueprint $table) {
            $table->foreignUuid('document_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('tag_id')->constrained()->onDelete('cascade');
            $table->primary(['document_id', 'tag_id']);
        });

        // category <-> folder
        Schema::create('category_folder', function (Blueprint $table) {
            $table->foreignUuid('category_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('folder_id')->constrained()->onDelete('cascade');
            $table->primary(['category_id', 'folder_id']);
        });

        // category <-> tag
        Schema::create('category_tag', function (Blueprint $table) {
            $table->foreignUuid('category_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('tag_id')->constrained()->onDelete('cascade');
            $table->primary(['category_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_tag');
        Schema::dropIfExists('category_folder');
        Schema::dropIfExists('document_tag');
        Schema::dropIfExists('folder_tag');
    }
};
