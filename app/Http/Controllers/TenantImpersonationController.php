<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantImpersonationController extends Controller
{
    /**
     * Single Sign-On / Impersonation for Central Admins into Tenant Workspaces.
     *
     * Issues a single-use stancl ImpersonationToken that logs the central admin
     * into the tenant workspace as their matching email account.  After login the
     * user is bounced through /sso-landing?ib={tenantId} which stores the source
     * tenant ID in the *tenant* session so LoginController::logout() can redirect
     * back to the tenant detail page when the impersonated session ends.
     *
     * SESSION_DOMAIN is intentionally blank (host-only cookies), so cross-domain
     * session sharing is not available.  The 'impersonated_by' value is threaded
     * through the redirect URL instead.
     */
    public function __invoke(Request $request, Tenant $tenant): RedirectResponse
    {
        // Require the user to be a central admin (enforced by middleware in route)
        /** @var \App\Models\User $centralUser */
        $centralUser = auth()->user();
        $centralEmail = $centralUser->email;

        // Query the isolated tenant database to find the equivalent user
        $tenantUserId = $tenant->run(function () use ($centralEmail) {
            return \App\Models\User::where('email', $centralEmail)->value('id');
        });

        if (!$tenantUserId) {
            return back()->with('error', "SSO Failed: No account found in the '{$tenant->organization_name}' workspace matching your central email ({$centralEmail}).");
        }

        // Thread the source tenant ID through the redirect URL so the tenant-side
        // /sso-landing route can store it in the tenant session after login.
        // We cannot use the central session for this because SESSION_DOMAIN is now
        // host-only — the central session cookie is not sent to tenant subdomains.
        $redirectPath = '/sso-landing?ib=' . rawurlencode($tenant->id);

        // Issue a single-use impersonation token valid for the tenant domain
        $token = tenancy()->impersonate($tenant, $tenantUserId, $redirectPath, 'web');

        $domain = $tenant->domains->firstOrFail()->domain;
        $scheme = $request->getScheme();
        $port = (int) $request->getPort();
        $portSuffix = !in_array($port, [80, 443], strict: true) ? ":{$port}" : '';

        return redirect("{$scheme}://{$domain}{$portSuffix}/impersonate/{$token->token}");
    }
}
