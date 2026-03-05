<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Trait ProtectsUuidRouteBindings
 *
 * Prevents PostgreSQL SQLSTATE[22P02] "invalid input syntax for type uuid" errors
 * by validating UUID format BEFORE attempting any database queries.
 *
 * This trait should be used on any Eloquent model with a UUID primary key that
 * might receive non-UUID values in route parameters (e.g., "admin" instead of
 * a valid UUID like "123e4567-e89b-12d3-a456-426614174000").
 *
 * Usage:
 *   use ProtectsUuidRouteBindings;
 *
 * How it works:
 * 1. resolveRouteBinding() returns null immediately for non-UUID strings (first line defense)
 * 2. Models can call ensureUuidValidForBinding() in their own resolveRouteBindingQuery()
 *    to provide additional query builder-level protection
 * 3. This two-tier approach prevents invalid UUIDs from reaching PostgreSQL's
 *    UUID type validation, preventing 22P02 errors
 *
 * Note on HasUuids collision:
 * This trait does NOT redefine resolveRouteBindingQuery() to avoid collision with
 * Illuminate\Database\Eloquent\Concerns\HasUuids. Models that need both protections
 * should explicitly override resolveRouteBindingQuery() and use this trait's
 * ensureUuidValidForBinding() helper method.
 */
trait ProtectsUuidRouteBindings
{
    /**
     * Surgically prevent Postgres 22P02 "invalid input syntax for type uuid"
     * when a non-UUID string (like "admin") is passed in a route parameter.
     *
     * This method is called FIRST and returns null immediately for invalid UUIDs,
     * preventing any further processing.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // Only validate the default field (usually the primary key)
        if ($field === null && !Str::isUuid($value)) {
            return null;
        }

        return parent::resolveRouteBinding($value, $field);
    }

    /**
     * Helper method for models to use in their own resolveRouteBindingQuery() override.
     *
     * This provides query builder-level protection against invalid UUIDs reaching
     * PostgreSQL. Models should call this in resolveRouteBindingQuery() to validate
     * the UUID before building the WHERE clause.
     *
     * Example usage in a model:
     *   public function resolveRouteBindingQuery($query = null, $value, $field = null)
     *   {
     *       if (is_null($query)) {
     *           $query = $this->newQuery();
     *       }
     *       return $this->ensureUuidValidForBinding($query, $value, $field)
     *           ?? parent::resolveRouteBindingQuery($query, $value, $field);
     *   }
     *
     * @param $query The query builder instance
     * @param $value The route parameter value
     * @param $field The field name (null for primary key)
     * @return \Illuminate\Database\Eloquent\Builder|null Returns query with WHERE 1=0 if invalid UUID, null if valid
     */
    protected function ensureUuidValidForBinding($query, $value, $field = null)
    {
        // Only validate the default field (usually the primary key)
        if (is_null($field) && !Str::isUuid($value)) {
            // Return the query with a condition that will never match
            // This prevents invalid UUIDs from reaching PostgreSQL's UUID validation
            return $query->whereRaw('1 = 0');
        }

        // Return null to indicate validation passed; let parent handle the query
        return null;
    }
}
