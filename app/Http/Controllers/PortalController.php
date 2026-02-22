<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use Illuminate\Http\Request;

/**
 * PortalController — Organisation Discovery Portal (central domain only).
 *
 * Provides the public landing page where end-users search for and navigate
 * to their organisation's EDMS login page without any knowledge of subdomains.
 *
 * Flow:
 *   Guest visits / → redirect /portal
 *   Portal shows ACTIVE orgs → user picks theirs → redirect to {slug}.domain/login
 *   Tenant login authenticates against the correct tenant DB
 *   LoginController::redirectTo() lands them on /home (RBAC dashboard)
 */
class PortalController extends Controller
{
    public function __invoke(Request $request): mixed
    {
        // Authenticated admins skip the portal — they belong in /admin/tenants
        if (auth()->check() && auth()->user()->isAdminOrAbove()) {
            return redirect('/admin/tenants');
        }

        // Load only ACTIVE tenants; suspended/inactive orgs must not be accessible.
        $tenants = Tenant::where('status', TenantStatus::ACTIVE)
            ->select(['id', 'organization_name', 'short_name', 'tenant_type'])
            ->with(['domains' => fn ($q) => $q->select(['domain', 'tenant_id'])->limit(1)])
            ->orderBy('organization_name')
            ->get();

        // Build absolute login URLs preserving the current scheme + non-standard port.
        $scheme     = $request->getScheme();
        $port       = (int) $request->getPort();
        $portSuffix = ! in_array($port, [80, 443], strict: true) ? ":{$port}" : '';

        $tenants = $tenants
            ->map(function (Tenant $tenant) use ($scheme, $portSuffix): Tenant {
                $domain            = $tenant->domains->first()?->domain;
                $tenant->login_url = $domain
                    ? "{$scheme}://{$domain}{$portSuffix}/login"
                    : null;

                return $tenant;
            })
            ->filter(fn (Tenant $t) => $t->login_url !== null)
            ->values();

        return view('portal.discover', compact('tenants'));
    }
}
