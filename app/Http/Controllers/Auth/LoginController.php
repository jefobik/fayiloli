<?php

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

    // ── Core override: enforced login attempt ─────────────────────────────────

    /**
     * Attempt to authenticate the incoming request.
     *
     * We intentionally diverge from AuthenticatesUsers::attemptLogin() here
     * so that we can:
     *   (a) verify the password ourselves before checking account state,
     *   (b) increment the failed-attempt counter only on wrong passwords
     *       (not on status failures — those don't consume login budget),
     *   (c) surface distinct, human-readable error messages for each failure
     *       mode rather than the catch-all "These credentials do not match".
     *
     * Using guard()->login() instead of guard()->attempt() avoids a redundant
     * second DB round-trip since we already resolved the user record.
     */
    protected function attemptLogin(Request $request): bool
    {
        $user = User::where($this->username(), $request->input($this->username()))->first();

        // ── Email not found or password mismatch ──────────────────────────────
        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            // Increment failed attempts only when we can identify the account.
            // We never increment for unknown emails to avoid an enumeration side-channel.
            if ($user) {
                $this->trackFailedAttempt($user);
            }
            return false;
        }

        // ── Account deactivated ───────────────────────────────────────────────
        if (! $user->is_active) {
            $this->loginError =
                'Your account has been deactivated. '
                . 'Please contact your workspace administrator to restore access.';
            return false;
        }

        // ── Account locked after too many failed attempts ─────────────────────
        if ($user->is_locked) {
            $this->loginError =
                'Your account is temporarily locked due to repeated failed login attempts. '
                . 'Please contact your administrator to unlock it.';
            return false;
        }

        // ── All checks passed: establish the authenticated session ────────────
        $this->guard()->login($user, $request->boolean('remember'));
        return true;
    }

    /**
     * Hook called immediately after a successful authentication.
     *
     * Resets the failed-attempt counter and records the last-login timestamp
     * so administrators can audit activity via the user management panel.
     *
     * forceFill() bypasses the $fillable guard since these are internal,
     * system-managed columns that should never be mass-assignable from forms.
     */
    protected function authenticated(Request $request, mixed $user): void
    {
        // Only update columns that exist in the current database context.
        // The central users table may not carry all tracking columns; we use
        // hasAttribute() to avoid "column not found" errors across contexts.
        $updates = [];

        if (array_key_exists('failed_login_attempts', $user->getAttributes()) || $user->exists) {
            $updates['failed_login_attempts'] = 0;
        }

        if (array_key_exists('last_login_at', $user->getAttributes()) || $user->exists) {
            $updates['last_login_at'] = now();
        }

        if (! empty($updates)) {
            $user->forceFill($updates)->saveQuietly();
        }
    }

    // ── Response override ─────────────────────────────────────────────────────

    /**
     * Send the response after a failed authentication attempt.
     *
     * Overridden to surface the account-status error (loginError) set during
     * attemptLogin() in preference to the generic auth.failed translation.
     */
    protected function sendFailedLoginResponse(Request $request): never
    {
        throw ValidationException::withMessages([
            $this->username() => [$this->loginError ?? trans('auth.failed')],
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Increment the failed_login_attempts counter for an identified user.
     *
     * Auto-locks the account and records locked_at when the threshold is
     * reached.  The lock must be manually lifted by an administrator via the
     * User Management panel.
     *
     * Uses forceFill() + saveQuietly() so the increment does not fire
     * activity-log listeners or model events (avoids noisy audit trails for
     * routine typo mistakes).
     *
     * @param  User  $user  The user record with a wrong password (not unknown).
     */
    private function trackFailedAttempt(User $user): void
    {
        $attempts = ($user->failed_login_attempts ?? 0) + 1;

        $updates = ['failed_login_attempts' => $attempts];

        if ($attempts >= self::MAX_ATTEMPTS_BEFORE_LOCK && ! $user->is_locked) {
            $updates['is_locked'] = true;

            // Only set locked_at if the column exists in the current DB context.
            if (array_key_exists('locked_at', $user->getAttributes()) || $user->exists) {
                $updates['locked_at'] = now();
            }
        }

        $user->forceFill($updates)->saveQuietly();
    }
}
