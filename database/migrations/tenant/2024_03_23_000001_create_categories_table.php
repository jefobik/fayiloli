<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->char('code', 10)->nullable();
            $table->timestamps();

            // Indexes
            $table->index('name', 'indx_categories_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
