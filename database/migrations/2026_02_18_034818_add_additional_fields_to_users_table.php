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
            $table->uuid('supervisor_id')->nullable()->before('name');
            $table->string('user_name',50)->unique()->before('email');
            $table->string('phone',15)->nullable()->after('email');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_2fa_enabled')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->softDeletes();

            //foreign key constraint for supervisor_id referencing id on the same users table
            $table->foreign('supervisor_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');

            //indexes for faster lookups
            $table->index('user_name', 'idx_users_user_name');
            $table->index('phone', 'idx_users_phone');
            $table->index('is_active', 'idx_users_is_active');
            $table->index('created_at', 'idx_users_created_at');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'supervisor_id', 'user_name', 'phone', 'phone_verified_at',
                'failed_login_attempts', 'last_login_at', 'is_admin',
                'is_active', 'is_locked', 'is_2fa_enabled', 'locked_at',
                'password_changed_at'
            ]);
            $table->dropSoftDeletes();
        });
    }
};
