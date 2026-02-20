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
        Schema::create('folders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->uuid('parent_id')->nullable();
            $table->bigInteger('position')->default(0);
            $table->foreign('parent_id')->references('id')->on('folders')->onDelete('cascade');
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->string('background_color')->nullable();
            $table->string('foreground_color')->nullable();
            $table->string('tags')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ==================== FOREIGN KEY CONSTRAINTS ====================
        // Separate schema call to avoid circular dependencies
        Schema::table('tenants', function (Blueprint $table) {
            // Self-referencing foreign key for parent-child relationship
            $table->foreign('parent_id')
                ->references('id')
                ->on('tenants')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};
