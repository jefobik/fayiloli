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
            $table->id();
            $table->string('name');
            $table->bigInteger('parent_id')->nullable();
            $table->bigInteger('position')->default(0);
            $table->foreign('parent_id')->references('id')->on('folders')->onDelete('cascade');
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->string('background_color')->nullable();
            $table->string('foreground_color')->nullable();
            $table->string('tags')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};
