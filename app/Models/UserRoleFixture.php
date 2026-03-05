<?php

/**
 * FIXTURE: Fix for Spatie Permissions UUID Role Query Issue
 *
 * When using User::role('admin'), Spatie's HasRoles trait queries the
 * model_has_roles pivot table to find users with that role. However,
 * if the role name isn't resolved to its UUID first, the query becomes:
 *
 *   SELECT * FROM users WHERE users.id IN (admin)
 *
 * This file documents the issue and the solution.
 *
 * PROBLEM:
 * --------
 * Spatie uses role_has_users relationship query which expects role_id (UUID).
 * When User::role('admin') is called but 'admin' (the name) isn't converted
 * to the UUID during the pivot  query, PostgreSQL receives:
 *   WHERE users.id in (admin)  -- admin is unquoted!
 *
 * This causes SQLSTATE[22P02] because 'admin' isNotA UUID.
 *
 * SOLUTION:
 * ---------
 * Override User::scopeRole() to ensure role names are resolved to UUIDs
 * before the pivot query is executed.The override captures the raw
 * scopeRole() query builder, validates/converts role name to UUID,
 * then proceeds with the Spatie logic.
 *
 * IMPLEMENTATION:
 * ---------------
 * In app/Models/User.php, add a scopeRole() method that:
 *
 *   public function scopeRole(Builder $query, string|array $roles, string $guard = null): Builder
 *   {
 *       $roleGuard = $guard ?? $this->getDefaultGuardName();
 *       $rolesParam = (array) $roles;
 *
 *       // Resolve role names to UUIDs before fetching the pivot
 *       $roleIds = Role::where('guard_name', $roleGuard)
 *           ->whereIn('name', $rolesParam)
 *           ->pluck('id')
 *           ->toArray();
 *
 *       // If no roles found, return empty query
 *       if (empty($roleIds)) {
 *           return $query->whereRaw('1 = 0');
 *       }
 *
 *       // Use the resolved UUIDs for the pivot query
 *       return $query->whereHas('roles', function ($q) use ($roleIds) {
 *           $q->whereIn('roles.id', $roleIds);
 *       });
 *   }
 *
 * This ensures only valid UUIDs are passed to the WHERE IN clause.
 *
 * TESTING:
 * --------
 * Test that User::role('admin')->get() returns users with that role.
 * Test that User::role('nonexistent')->get() returns empty collection.
 * Verify no SQLSTATE[22P02] errors occur.
 */
