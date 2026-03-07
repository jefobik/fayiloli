<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Creates the user_preferences table in the central database to support
     * user-level settings and preferences for both central admins and tenant users
     * that are synced across workspaces.
     *
     * This table mirrors the tenant version and is managed by UserPreference model.
     */
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->index();
            $table->string('key', 255)->index();
            $table->text('value')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            // Composite unique index to prevent duplicate preference keys per user
            $table->unique(['user_id', 'key'], 'user_preferences_user_id_key_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
