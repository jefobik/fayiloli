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
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('supervisor_id')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->softDeletes();

            // Foreign key: supervisor_id → users.id
            $table->foreign('supervisor_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');

            // Index on created_at only — user_name, phone, is_active are added by later migrations
            $table->index('created_at', 'idx_users_created_at');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key and index before dropping columns
            if (Schema::hasColumn('users', 'supervisor_id')) {
                $table->dropForeign(['supervisor_id']);
            }
            $table->dropIndex('idx_users_created_at');
        });

        // Drop only columns this migration owns; later migrations' down() may have
        // already removed some (e.g. failed_login_attempts, last_login_at, deleted_at).
        $columns = array_filter(
            ['supervisor_id', 'phone_verified_at', 'failed_login_attempts', 'last_login_at', 'password_changed_at', 'deleted_at'],
            fn($col) => Schema::hasColumn('users', $col)
        );

        if ($columns) {
            Schema::table('users', function (Blueprint $table) use ($columns) {
                $table->dropColumn(array_values($columns));
            });
        }
    }
};
