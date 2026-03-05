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
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

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
        'theme',
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
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'is_locked' => 'boolean',
            'is_2fa_enabled' => 'boolean',
            // Tracking columns — exist in tenant users table; central table gains
            // them via migration 2026_02_22_000001_add_tracking_to_central_users.php
            'last_login_at' => 'datetime',
            'locked_at' => 'datetime',
            'deleted_at' => 'datetime',
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

    // ── Relationships ─────────────────────────────────────────────────────────

    public function supervisor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function supervisees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    // ── Computed attributes ───────────────────────────────────────────────────

    public function getAvatarInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = collect($words)->take(2)->map(fn($w) => strtoupper($w[0]))->implode('');
        return $initials ?: 'U';
    }

    public function preferences(): HasMany
    {
        return $this->hasMany(UserPreference::class);
    }

    /**
     * Override Spatie's scopeRole() to ensure role names are resolved to UUIDs
     * before the pivot table query is executed.
     *
     * When using User::role('admin'), this method ensures 'admin' (the name)
     * is resolved to its UUID before being used in a WHERE IN clause.
     * This prevents SQLSTATE[22P02] errors with PostgreSQL.
     */
    public function scopeRole(Builder $query, string|array $roles = [], string $guard = null): Builder
    {
        $roleGuard = $guard ?? $this->getDefaultGuardName();
        $rolesParam = (array) $roles;

        // Resolve role names to UUIDs before fetching the pivot data
        $roleIds = Role::where('guard_name', $roleGuard)
            ->whereIn('name', $rolesParam)
            ->pluck('id')
            ->toArray();

        // If no roles found, return an empty query builder
        if (empty($roleIds)) {
            return $query->whereRaw('1 = 0');
        }

        // Use the resolved UUIDs for the pivot query
        return $query->whereHas('roles', function (Builder $q) use ($roleIds) {
            $q->whereIn('roles.id', $roleIds);
        });
    }

    /**
     * Safely retrieve a single preference value.
     * Returns $default if the table does not exist (central DB context) or the key is not found.
     */
    public function getPreference(string $key, mixed $default = null): mixed
    {
        try {
            return $this->preferences()->where('key', $key)->value('value') ?? $default;
        } catch (\Throwable) {
            return $default;
        }
    }

    /**
     * Persist (upsert) a single preference key → value.
     *
     * Silently swallows errors when:
     *   - The user_preferences table does not exist (central DB — tenant migration
     *     has not run there).
     *   - Any other transient DB failure (preferences must never crash a UI action).
     *
     * The unique(user_id, key) constraint in the migration guarantees a clean
     * upsert with no duplicate rows regardless of request concurrency.
     */
    public function setPreference(string $key, mixed $value): void
    {
        try {
            $this->preferences()->updateOrCreate(
                ['key'   => $key],
                ['value' => $value],
            );
        } catch (\Throwable) {
            // Central context or table missing — silently ignore
        }
    }

    /**
     * Surgically prevent Postgres 22P02 "invalid input syntax for type uuid"
     * when a non-UUID string (like "admin") is passed in a route parameter.
     *
     * This method is called FIRST before the database query is executed,
     * preventing invalid UUIDs from ever reaching PostgreSQL.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field === null && !\Illuminate\Support\Str::isUuid($value)) {
            return null;
        }

        return parent::resolveRouteBinding($value, $field);
    }

    /**
     * Override the route binding query to validate UUID format before querying the database.
     *
     * This prevents PostgreSQL from throwing SQLSTATE[22P02] errors when invalid
     * UUID values are passed to the WHERE clause. The validation happens at the
     * query builder level, before any database round-trip.
     */
    public function resolveRouteBindingQuery($query = null, $value, $field = null)
    {
        // If no query builder provided, create one from the model
        if (is_null($query)) {
            $query = $this->newQuery();
        }

        // Validate UUID format before adding to WHERE clause
        // This prevents "invalid input syntax for type uuid" errors from PostgreSQL
        if (is_null($field) && !\Illuminate\Support\Str::isUuid($value)) {
            // Return a query that will never match anything
            return $query->whereRaw('1 = 0');
        }

        return parent::resolveRouteBindingQuery($query, $value, $field);
    }
}
