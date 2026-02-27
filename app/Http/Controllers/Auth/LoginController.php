<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * LoginController — Tenant-Aware Authentication.
 *
 * Extends Laravel's AuthenticatesUsers with enterprise-grade controls:
 *
 *  1. Tenant DB isolation — InitializeTenancyByDomain (prepended to the
 *     global web group) switches the DB connection before this controller
 *     is ever reached.  User::where('email', ...) always queries the
 *     correct database: tenant DB on tenant domains, central DB on the
 *     platform admin domain.
 *
 *  2. Account-status enforcement — is_active and is_locked are checked
 *     after password verification so we can return actionable error messages
 *     instead of the generic "credentials do not match".
 *
 *  3. Failed-attempt tracking — failed_login_attempts is incremented on
 *     each wrong-password attempt; the account is auto-locked after 5
 *     consecutive failures.  The counter resets on successful login.
 *
 *  4. Audit trail — last_login_at is updated on every successful login so
 *     administrators can monitor account activity.
 *
 *  5. Post-login redirect — tenant domains land on /home (the personalised
 *     RBAC dashboard); the central admin domain lands on /admin/tenants.
 */
class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Maximum consecutive wrong-password attempts before account auto-lock.
     */
    protected const MAX_ATTEMPTS_BEFORE_LOCK = 5;

    /**
     * Specific error message set by attemptLogin() when account status blocks
     * authentication.  Surfaced by sendFailedLoginResponse() to the view.
     */
    protected ?string $loginError = null;

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Post-login redirect destination.
     *
     * Tenant domain  → /home  (module launchpad dashboard)
     * Central domain → /admin/tenants  (platform administration)
     */
    protected function redirectTo(): string
    {
        return tenancy()->initialized ? '/home' : '/admin/tenants';
    }

    /**
     * Hook called immediately after a successful authentication.
     *
     * In both central and tenant scopes, we must check if the account is
     * active or locked. If it is deactivated/locked, we log them out
     * immediately and throw a descriptive ValidationException.
     *
     * We also reset the failed_login_attempts counter and record the
     * last_login timestamp so administrators can audit activity.
     */
    protected function authenticated(Request $request, mixed $user): void
    {
        // ── Account deactivated ───────────────────────────────────────────────
        if (!$user->is_active) {
            $this->guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                $this->username() => ['Your account has been deactivated. Please contact your workspace administrator to restore access.'],
            ]);
        }

        // ── Account locked after too many failed attempts ─────────────────────
        if ($user->is_locked) {
            $this->guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                $this->username() => ['Your account is temporarily locked due to repeated failed login attempts. Please contact your administrator to unlock it.'],
            ]);
        }

        // Only update columns that exist in the current database context.
        // The central users table may not carry all tracking columns; we use
        // hasAttribute() to avoid "column not found" errors across contexts.
        $updates = [];

        if (array_key_exists('failed_login_attempts', $user->getAttributes())) {
            $updates['failed_login_attempts'] = 0;
        }

        if (array_key_exists('last_login_at', $user->getAttributes())) {
            $updates['last_login_at'] = now();
        }

        if (!empty($updates)) {
            $user->forceFill($updates)->saveQuietly();
        }
    }




}
