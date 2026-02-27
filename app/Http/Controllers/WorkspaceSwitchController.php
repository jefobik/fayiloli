<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Cross-workspace SSO switch for tenant admin users.
 *
 * A tenant admin clicks "Switch Workspace" in the app header → this controller
 * resolves the target workspace → finds the user's account in the target
 * tenant's isolated database (matched by email) → issues a stancl single-use
 * ImpersonationToken → redirects the browser to:
 *
 *   {scheme}://{target-domain}{port}/impersonate/{token}
 *
 * The /impersonate/{token} route on the target domain (registered in
 * routes/tenant.php) calls UserImpersonation::makeResponse(), which
 * logs the user in and redirects them to /home.
 *
 * Constraints:
 *   • Requires auth + isAdminOrAbove() in the current workspace.
 *   • The user must have an account with the same email in the target workspace.
 *   • The target workspace must be ACTIVE.
 *   • The ImpersonationToken is stored in the central DB; tenancy()->central()
 *     is used to ensure the write runs against the central connection even
 *     though this controller runs on a tenant domain.
 *
 * Route: GET /switch-workspace/{tenantId}  (tenant domain)
 */
class WorkspaceSwitchController extends Controller
{
    public function __invoke(Request $request, string $tenantId): RedirectResponse
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();

        abort_unless($currentUser->isAdminOrAbove(), 403, 'Workspace switching requires admin access.');

        // Tenant model uses the central_connection — safe to query from tenant context.
        $target = Tenant::with('domains')
            ->where('id', $tenantId)
            ->where('status', TenantStatus::ACTIVE)
            ->first();

        if (! $target) {
            return back()->with('error', 'The requested workspace is unavailable or inactive.');
        }

        // Guard: prevent switching to the current workspace (no-op).
        if ($target->id === tenancy()->tenant?->id) {
            return back();
        }

        // Look up the user's account in the TARGET tenant's isolated database.
        // $target->run() temporarily switches the default DB connection to the
        // target tenant's database, executes the callback, then switches back.
        $email        = $currentUser->email;
        $targetUserId = $target->run(function () use ($email): ?string {
            return \App\Models\User::where('email', $email)->value('id');
        });

        if (! $targetUserId) {
            return back()->with(
                'error',
                "You don't have an account in \"{$target->organization_name}\". "
                . 'Contact that workspace admin to be invited.'
            );
        }

        // Issue a single-use impersonation token.
        // tenancy()->central() reverts to the central DB connection for the duration
        // of the callback so ImpersonationToken::create() writes to the correct table,
        // then re-initialises the original tenant context before returning.
        $token = tenancy()->central(function () use ($target, $targetUserId): \Stancl\Tenancy\Database\Models\ImpersonationToken {
            return tenancy()->impersonate($target, $targetUserId, '/home', 'web');
        });

        $domain     = $target->domains->first()?->domain;
        $scheme     = $request->getScheme();
        $port       = (int) $request->getPort();
        $portSuffix = ! in_array($port, [80, 443], strict: true) ? ":{$port}" : '';

        return redirect("{$scheme}://{$domain}{$portSuffix}/impersonate/{$token->token}");
    }
}
