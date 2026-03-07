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
     * Attempt to authenticate the user — overrides AuthenticatesUsers default.
     *
     * The trait's default implementation calls guard()->attempt() which creates
     * a session for ANY user whose password matches — including deactivated and
     * locked accounts.  authenticated() then has to tear that session down,
     * which is a TOCTOU window.
     *
     * This override instead:
     *   1. Resolves the User record by email first (no session created yet).
     *   2. Validates the password with Hash::check().
     *   3. Enforces is_active and is_locked BEFORE a session is ever issued.
     *   4. On wrong password, increments failed_login_attempts and auto-locks
     *      the account after MAX_ATTEMPTS_BEFORE_LOCK consecutive failures.
     *   5. On success, calls guard()->login() directly — no second DB round-trip.
     */
    protected function attemptLogin(Request $request): bool
    {
        $credentials = $this->credentials($request);
        $user        = User::where($this->username(), $credentials[$this->username()])->first();

        // ── Unknown email or wrong password ───────────────────────────────────
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            if ($user) {
                $this->trackFailedAttempt($user);
            }
            return false;
        }

        // ── Account deactivated ───────────────────────────────────────────────
        if (!$user->is_active) {
            $this->loginError = 'Your account has been deactivated. Please contact your workspace administrator to restore access.';
            return false;
        }

        // ── Account locked ────────────────────────────────────────────────────
        if ($user->is_locked) {
            $this->loginError = 'Your account is temporarily locked due to repeated failed login attempts. Please contact your administrator to unlock it.';
            return false;
        }

        // ── Successful authentication — issue session ─────────────────────────
        $this->guard()->login($user, $request->boolean('remember'));
        return true;
    }

    /**
     * Track a failed login attempt and auto-lock after the threshold.
     *
     * Uses saveQuietly() to avoid triggering the LogsActivity observer on
     * every failed attempt, keeping MongoDB writes reserved for meaningful
     * state changes initiated by the application (not bots or typos).
     */
    protected function trackFailedAttempt(User $user): void
    {
        $attempts = ($user->failed_login_attempts ?? 0) + 1;
        $updates  = ['failed_login_attempts' => $attempts];

        if ($attempts >= static::MAX_ATTEMPTS_BEFORE_LOCK) {
            $updates['is_locked'] = true;
            $updates['locked_at'] = now();
            $this->loginError     = 'Your account has been locked after ' . static::MAX_ATTEMPTS_BEFORE_LOCK
                . ' failed attempts. Please contact your administrator.';
        }

        $user->forceFill($updates)->saveQuietly();
    }

    /**
     * Send the failed login response back to the client.
     *
     * Surfaces the account-status error stored in $loginError when the
     * failure was caused by account state (deactivated / locked) rather
     * than a bad password.  Falls back to the generic auth.failed message
     * for wrong-credential attempts so we don't leak whether the email exists.
     */
    protected function sendFailedLoginResponse(Request $request): void
    {
        throw ValidationException::withMessages([
            $this->username() => [$this->loginError ?? trans('auth.failed')],
        ]);
    }

    /**
     * Hook called immediately after a successful authentication.
     *
     * attemptLogin() already guards against deactivated/locked accounts before
     * a session is issued, so these checks here are defence-in-depth only
     * (e.g. account deactivated between the two calls in a race condition).
     *
     * Primary responsibility: reset the failed-attempt counter and record
     * last_login_at for the administrator audit trail.
     */
    protected function authenticated(Request $request, mixed $user): void
    {
        // ── Defence-in-depth: account deactivated ─────────────────────────────
        if (!$user->is_active) {
            $this->guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                $this->username() => ['Your account has been deactivated. Please contact your workspace administrator to restore access.'],
            ]);
        }

        // ── Defence-in-depth: account locked ─────────────────────────────────
        if ($user->is_locked) {
            $this->guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                $this->username() => ['Your account is temporarily locked due to repeated failed login attempts. Please contact your administrator to unlock it.'],
            ]);
        }

        // ── Audit trail — reset counter + record timestamp ────────────────────
        // Guard against central DB context where tracking columns may not exist
        // (they are added by an additive migration; Schema::hasColumn() guards
        // prevent the migration from failing, but a fresh central DB that has
        // not yet run the additive migration will be missing these columns).
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

    /**
     * Sign the user out of the application.
     *
     * Overrides AuthenticatesUsers::logout() so we can inspect the session
     * BEFORE it is invalidated.  The trait's implementation invalidates the
     * session first and then calls loggedOut() — by which point any session
     * data (like 'impersonated_by') is already gone.
     *
     * Redirect priority (Fix #6):
     *  1. Impersonation: central admin who jumped into a tenant → tenant detail page.
     *  2. Tenant context: regular sign-out → tenant-scoped login page.
     *  3. Central context: admin sign-out → /portal (public discovery page).
     */
    public function logout(Request $request): mixed
    {
        // ── Capture before the session is wiped ──────────────────────────────
        $impersonatedBy = $request->session()->get('impersonated_by');

        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ── Redirect ─────────────────────────────────────────────────────────
        if ($impersonatedBy) {
            return redirect()->route('tenants.show', $impersonatedBy);
        }

        if (tenancy()->initialized) {
            return redirect()->route('login');
        }

        return redirect('/portal');
    }
}
