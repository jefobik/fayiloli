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
            $table->uuid('id')->primary();
            $table->bigInteger('position')->default(0);
            $table->string('name')->nullable();
            $table->string('slug')->nullable()->index();
            $table->string('original_name')->nullable();
            $table->string('file_path')->nullable();
            $table->bigInteger('size')->nullable();
            $table->string('extension')->nullable();
            $table->foreignUuid('folder_id')
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

            // Indexes
            $table->index('created_at', 'indx_documents_created_at');
            $table->index(['name', 'slug'], 'indx_documents_name_slug');
            $table->index('visibility', 'indx_documents_visibility');
            $table->index('folder_id', 'indx_documents_folder_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
