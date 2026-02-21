<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->char('code', 10)->nullable();
            $table->string('background_color')->nullable();
            $table->string('foreground_color')->nullable();
            $table->foreignUuid('category_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->timestamps();

            // Indexes
            $table->index('name', 'indx_tags_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
