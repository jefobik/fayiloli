<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('position')->default(0);
            $table->string('name')->nullable();
            $table->string('slug')->nullable()->index();
            $table->string('original_name')->nullable();
            $table->string('file_path')->nullable();
            $table->bigInteger('size')->nullable();
            $table->string('extension')->nullable();
            $table->foreignId('folder_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->string('visibility')->default('public');
            $table->bigInteger('share')->default(0);
            $table->bigInteger('download')->default(0);
            $table->string('email')->nullable();
            $table->string('url')->nullable();
            $table->string('contact')->nullable();
            $table->string('owner')->nullable();
            $table->timestamp('date')->nullable();
            $table->string('emojies')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
