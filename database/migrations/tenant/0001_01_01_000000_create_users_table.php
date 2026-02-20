<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tenant-scoped users table.
 *
 * Each tenant database receives its own `users` table that is entirely
 * independent from all other tenants and from the central database.
 * Includes all EDMS-specific user fields added by the central migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('supervisor_id')->nullable();
            $table->string('name');
            $table->string('user_name', 50)->unique();
            $table->string('email')->unique();
            $table->string('phone', 15)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password');
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_2fa_enabled')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            //indexes
            $table->index('supervisor_id', 'indx_users_supervisor_id');
            $table->index('email', 'indx_users_email');
            $table->index('name', 'indx_users_name');
            $table->index('user_name', 'indx_users_user_name');
            $table->index('is_active', 'indx_users_is_active');
            $table->index('created_at', 'indx_users_created_at');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
