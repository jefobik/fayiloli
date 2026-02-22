<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable, HasRoles, LogsActivity, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'user_name',
        'email',
        'phone',
        'password',
        'supervisor_id',        // tenant-context only (foreign key within tenant DB)
        'is_super_admin',       // central-context: bypasses all Gate checks
        'is_admin',             // central-context: central admin portal access
        'is_active',
        'is_locked',
        'is_2fa_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_super_admin'    => 'boolean',
            'is_admin'          => 'boolean',
            'is_active'         => 'boolean',
            'is_locked'         => 'boolean',
            'is_2fa_enabled'    => 'boolean',
            // Tracking columns — exist in tenant users table; central table gains
            // them via migration 2026_02_22_000001_add_tracking_to_central_users.php
            'last_login_at'     => 'datetime',
            'locked_at'         => 'datetime',
            'deleted_at'        => 'datetime',
        ];
    }

    // ── Role helpers ──────────────────────────────────────────────────────────

    /**
     * True when this user holds the platform super-admin flag.
     *
     * Super-admins bypass every Gate check (registered in AppServiceProvider
     * via Gate::before()).  This method is provided for explicit conditional
     * checks in views and controllers where the bypass does not apply.
     */
    public function isSuperAdmin(): bool
    {
        return (bool) ($this->is_super_admin ?? false);
    }

    /**
     * True when the user has at least admin-level access to the central portal.
     * Super-admins implicitly satisfy this check.
     */
    public function isAdminOrAbove(): bool
    {
        return $this->isSuperAdmin() || (bool) ($this->is_admin ?? false);
    }

    /**
     * Human-readable role label used in UI components.
     */
    public function roleLabel(): string
    {
        if ($this->isSuperAdmin()) {
            return 'Super Admin';
        }
        if ($this->isAdminOrAbove()) {
            return 'Admin';
        }
        return 'User';
    }

    // ── Activity log ──────────────────────────────────────────────────────────

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'is_admin', 'is_super_admin'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('user');
    }

    // ── Computed attributes ───────────────────────────────────────────────────

    public function getAvatarInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = collect($words)->take(2)->map(fn ($w) => strtoupper($w[0]))->implode('');
        return $initials ?: 'U';
    }
}
