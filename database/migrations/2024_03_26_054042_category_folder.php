<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('category_folder', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('category_id');
            $table->uuid('folder_id');

            $table->timestamps();

            // Add foreign key constraints
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');

            // Add unique constraint to ensure each tag is associated with a folder only once
            $table->unique(['category_id', 'folder_id']);

            // Add indexes for faster lookups
            $table->index('category_id', 'idx_category_folder_category_id');
            $table->index('folder_id', 'idx_category_folder_folder_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_folder');
    }
};
