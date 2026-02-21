<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('folders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->uuid('parent_id')->nullable();
            $table->bigInteger('position')->default(0);
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->string('background_color')->nullable();
            $table->string('foreground_color')->nullable();
            $table->string('tags')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('parent_id', 'indx_folders_parent_id');
            $table->index('visibility', 'indx_folders_visibility');
            $table->index('name', 'indx_folders_name');
        });

        // ==================== FOREIGN KEY CONSTRAINTS ====================
        // Separate schema call so the primary key on `id` is created first,
        // which PostgreSQL requires before a FK can reference it.
        Schema::table('folders', function (Blueprint $table) {
            $table->foreign('parent_id')
                ->references('id')
                ->on('folders')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};
