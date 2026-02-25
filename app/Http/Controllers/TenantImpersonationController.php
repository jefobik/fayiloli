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
     */
    public function __invoke(Request $request, Tenant $tenant): RedirectResponse
    {
        // Require the user to be a central admin (enforced by middleware in route)
        $centralEmail = auth()->user()->email;

        // Query the isolated tenant database to find the equivalent user
        $tenantUserId = $tenant->run(function () use ($centralEmail) {
            return \App\Models\User::where('email', $centralEmail)->value('id');
        });

        if (!$tenantUserId) {
            return back()->with('error', "SSO Failed: No account found in the '{$tenant->organization_name}' workspace matching your central email ({$centralEmail}).");
        }

        // Issue a single-use impersonation token valid for the tenant domain
        $token = tenancy()->impersonate($tenant, $tenantUserId, '/home', 'web');

        $domain = $tenant->domains->firstOrFail()->domain;
        $scheme = $request->getScheme();
        $port = (int) $request->getPort();
        $portSuffix = !in_array($port, [80, 443], strict: true) ? ":{$port}" : '';

        return redirect("{$scheme}://{$domain}{$portSuffix}/impersonate/{$token->token}");
    }
}
