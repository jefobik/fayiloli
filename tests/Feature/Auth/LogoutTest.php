<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature tests for the sign-out flow across all tenant-aware contexts.
 *
 * CSRF bypass: Laravel 12 uses ValidateCsrfToken (not VerifyCsrfToken).
 * withoutMiddleware(ValidateCsrfToken::class) is applied per-test so we can
 * assert redirect destinations cleanly.  The dedicated CSRF test does NOT
 * bypass middleware and must receive 419.
 */
class LogoutTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────────────────
    // Central-domain sign-out
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function central_user_is_redirected_to_portal_after_signout(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->withoutMiddleware(ValidateCsrfToken::class)
            ->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect('/portal');
    }

    #[Test]
    public function session_data_is_cleared_on_signout(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        // Plant a sentinel value and confirm it is gone after logout
        $response = $this->withoutMiddleware(ValidateCsrfToken::class)
            ->actingAs($user)
            ->withSession(['sentinel' => 'alive'])
            ->post(route('logout'));

        // After session()->invalidate() the sentinel must not survive
        $response->assertSessionMissing('sentinel');
    }

    #[Test]
    public function request_without_csrf_token_is_rejected_with_419(): void
    {
        // Middleware IS active for this test — must block unauthenticated POST
        $this->post('/logout')->assertStatus(419);
    }

    #[Test]
    public function authenticated_user_is_logged_out_after_signout(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->withoutMiddleware(ValidateCsrfToken::class)
            ->actingAs($user)
            ->post(route('logout'));

        // After the call the guard model in the application must be null
        $this->assertNull(auth()->user());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Impersonation-aware sign-out (Fix #6)
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function impersonation_signout_redirects_to_tenant_detail_page(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $fakeTenantId = 'acme-corp';

        // Pre-seed the session key that TenantImpersonationController writes
        // before issuing the cross-domain token redirect.
        $this->withoutMiddleware(ValidateCsrfToken::class)
            ->actingAs($user)
            ->withSession(['impersonated_by' => $fakeTenantId])
            ->post(route('logout'))
            ->assertRedirect(route('tenants.show', $fakeTenantId));
    }

    #[Test]
    public function signout_without_impersonation_key_falls_back_to_portal(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->withoutMiddleware(ValidateCsrfToken::class)
            ->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect('/portal');
    }
}
