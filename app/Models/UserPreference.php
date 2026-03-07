<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserPreference — User-level settings and preferences
 *
 * Stores key-value preference pairs for users across central and tenant contexts.
 * Preferences persist similarly across workspaces if a user has accounts in multiple tenants.
 *
 * Example usage:
 *   $user->setPreference('theme', 'dark')
 *   $user->getPreference('theme', 'light')  // Returns 'dark'
 *
 * Architecture:
 *   - One UserPreference per (user_id, key) pair — the unique constraint guarantees this
 *   - Central database: preferences for central admins
 *   - Tenant database: preferences for tenant users or synced from central
 *   - Silent failures: CRUD operations never crash if the table is missing (central DB)
 */
class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns this preference.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Set the user that owns this preference.
     *
     * @param User $user
     * @return void
     */
    public function setUser(User $user): void
    {
        $this->user()->associate($user);
    }

    /**
     * Retrieve a preference value safely, with fallback support.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        try {
            return self::where('key', $key)->value('value') ?? $default;
        } catch (\Throwable) {
            return $default;
        }
    }

    /**
     * Store a preference value with upsert semantics.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function setValue(string $key, mixed $value): void
    {
        try {
            self::updateOrCreate(
                ['key' => $key],
                ['value' => $value],
            );
        } catch (\Throwable) {
            // Silent failure — preferences must never crash application flow
        }
    }
}

