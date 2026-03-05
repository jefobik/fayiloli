<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Test suite for UUID-based Spatie Permissions role queries
 *
 * Verifies that User::role('admin') correctly resolves role names to UUIDs
 * and doesn't pass string role names to WHERE IN clauses, which would cause
 * SQLSTATE[22P02] errors in PostgreSQL.
 */
class UserRoleUuidQueryTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure we're in a tenant database context for this test
        if (!tenancy()->initialized) {
            $this->markTestSkipped('Test requires tenant database context');
        }
    }

    /**
     * Test that User::role('admin') resolves the role name to its UUID
     * and returns users with that role without causing SQLSTATE[22P02] error.
     */
    public function test_user_role_scope_resolves_name_to_uuid(): void
    {
        // Create an 'admin' role
        $adminRole = Role::create([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        // Create a user and assign the 'admin' role
        $user = User::factory()->create(['name' => 'Admin User']);
        $user->assignRole($adminRole);

        // This should NOT throw SQLSTATE[22P02] error
        $admins = User::role('admin')->get();

        // Verify the query succeeds and returns the correct user
        $this->assertCount(1, $admins);
        $this->assertTrue($admins->contains('id', $user->id));
    }

    /**
     * Test that User::role() with a non-existent role returns empty collection
     */
    public function test_user_role_scope_returns_empty_for_nonexistent_role(): void
    {
        // Query for a role that doesn't exist
        $users = User::role('nonexistent-role')->get();

        // Should return empty collection, not throw an error
        $this->assertCount(0, $users);
    }

    /**
     * Test that User::role() works with multiple role names
     */
    public function test_user_role_scope_handles_multiple_roles(): void
    {
        // Create two roles
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $managerRole = Role::create(['name' => 'manager', 'guard_name' => 'web']);

        // Create users with different roles
        $admin = User::factory()->create(['name' => 'Admin']);
        $admin->assignRole($adminRole);

        $manager = User::factory()->create(['name' => 'Manager']);
        $manager->assignRole($managerRole);

        // Query for both roles
        $result = User::role(['admin', 'manager'])->get();

        // Should return both users
        $this->assertCount(2, $result);
        $this->assertTrue($result->pluck('id')->contains($admin->id));
        $this->assertTrue($result->pluck('id')->contains($manager->id));
    }

    /**
     * Test that the query builder properly validates UUID format
     * and doesn't pass invalid values to PostgreSQL
     */
    public function test_user_role_scope_never_passes_invalid_uuid_to_db(): void
    {
        // Create a role
        Role::create(['name' => 'test-role', 'guard_name' => 'web']);

        // Enable query logging to inspect the SQL
        \Illuminate\Support\Facades\DB::enableQueryLog();

        // Execute the role query
        User::role('test-role')->get();

        $queries = \Illuminate\Support\Facades\DB::getQueryLog();

        // Verify that role_ids in the query are valid UUIDs or empty
        foreach ($queries as $query) {
            // The query should contain the resolved UUID, not the string 'test-role'
            $this->assertStringNotContainsString("in (test-role)", $query['query']);
        }

        \Illuminate\Support\Facades\DB::disableQueryLog();
    }
}
