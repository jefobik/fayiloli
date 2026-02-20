<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->string('request_to')->nullable();
            $table->foreignUuid('folder_id')->nullable()->constrained('folders')->onDelete('cascade');
            $table->foreignUuid('tag_id')->nullable()->constrained('tags')->onDelete('cascade');
            $table->bigInteger('due_date_in_number')->nullable();
            $table->string('due_date_in_word')->nullable();
            $table->longText('note')->nullable();
            $table->timestamps();

            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_requests');
    }
};
