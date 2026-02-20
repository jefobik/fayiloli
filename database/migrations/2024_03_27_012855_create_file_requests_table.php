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
        Schema::create('file_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->string('request_to')->nullable();
            $table->uuid('folder_id')->nullable();
            $table->uuid('tag_id')->nullable();
            $table->bigInteger('due_date_in_number')->nullable();
            $table->string('due_date_in_word')->nullable();
            $table->longText('note')->nullable();
            $table->timestamps();

            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');

            //indexes for faster lookups
            $table->index('name', 'idx_file_requests_name');
            $table->index('folder_id', 'idx_file_requests_folder_id');
            $table->index('tag_id', 'idx_file_requests_tag_id');
            $table->index('created_at', 'idx_file_requests_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_requests');
    }
};
