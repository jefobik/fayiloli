<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * UserPreferenceService — Enterprise-grade user preference management
 *
 * Provides a clean interface for managing user preferences with:
 *   - Type-safe getters and setters
 *   - Caching support for performance
 *   - Graceful error handling for central/tenant contexts
 *   - Logging for audit trails
 *
 * Example usage:
 *   $service = new UserPreferenceService($user);
 *   $service->get('theme', 'light')     // Get with default
 *   $service->set('theme', 'dark')      // Set with upsert
 *   $service->delete('theme')           // Delete a preference
 *   $service->getAll()                  // Get all preferences as array
 */
class UserPreferenceService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const CACHE_PREFIX = 'user_preferences_';

    public function __construct(private readonly User $user)
    {
    }

    /**
     * Retrieve a single preference value with optional default.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        try {
            // Try cache first
            $cacheKey = $this->getCacheKey($key);
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            // Query database
            $value = $this->user
                ->preferences()
                ->where('key', $key)
                ->value('value') ?? $default;

            // Cache the result
            Cache::put($cacheKey, $value, self::CACHE_TTL);

            return $value;
        } catch (\Throwable $e) {
            Log::debug('UserPreference retrieval failed', [
                'user_id' => $this->user->id,
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return $default;
        }
    }

    /**
     * Store or update a preference value.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function set(string $key, mixed $value): bool
    {
        try {
            $this->user->preferences()->updateOrCreate(
                ['key' => $key],
                ['value' => $value],
            );

            // Invalidate cache
            Cache::forget($this->getCacheKey($key));

            Log::debug('UserPreference updated', [
                'user_id' => $this->user->id,
                'key' => $key,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('UserPreference update failed', [
                'user_id' => $this->user->id,
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Delete a preference.
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        try {
            $this->user
                ->preferences()
                ->where('key', $key)
                ->delete();

            // Invalidate cache
            Cache::forget($this->getCacheKey($key));

            Log::debug('UserPreference deleted', [
                'user_id' => $this->user->id,
                'key' => $key,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('UserPreference deletion failed', [
                'user_id' => $this->user->id,
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Retrieve all preferences as a key-value array.
     *
     * @return array<string, mixed>
     */
    public function getAll(): array
    {
        try {
            return $this->user
                ->preferences()
                ->pluck('value', 'key')
                ->toArray();
        } catch (\Throwable $e) {
            Log::debug('UserPreference retrieval all failed', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Check whether a preference exists.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        try {
            return $this->user
                ->preferences()
                ->where('key', $key)
                ->exists();
        } catch (\Throwable $e) {
            Log::debug('UserPreference existence check failed', [
                'user_id' => $this->user->id,
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Delete all preferences for this user.
     *
     * @return bool
     */
    public function deleteAll(): bool
    {
        try {
            $this->user->preferences()->delete();

            // Clear all cache keys for this user
            Cache::tags(['user_' . $this->user->id])->flush();

            Log::debug('All UserPreferences deleted', [
                'user_id' => $this->user->id,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('UserPreference deletion all failed', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Generate cache key for a specific preference.
     *
     * @param string $key
     * @return string
     */
    private function getCacheKey(string $key): string
    {
        return self::CACHE_PREFIX . $this->user->id . '_' . $key;
    }
}
