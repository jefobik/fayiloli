<?php

declare(strict_types=1);

namespace App\Livewire\Layouts;

use Livewire\Component;
use App\Models\Tenant;
use App\Enums\TenantStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class WorkspaceSwitcher extends Component
{
    /**
     * Visual colour pairs (gradient start, gradient end) keyed by tenant_type value.
     * Matches the GW workspace-switcher design system.
     */
    public array $wsTypeColors = [
        'government'  => ['#dc2626', '#b91c1c'],
        'secretariat' => ['#4f46e5', '#4338ca'],
        'agency'      => ['#0284c7', '#0369a1'],
        'department'  => ['#16a34a', '#15803d'],
        'unit'        => ['#d97706', '#b45309'],
    ];

    /**
     * Return all ACTIVE tenants the current user can switch to.
     *
     * Context note: this runs from within a tenant Livewire component so the active
     * DB connection is the tenant DB. stancl/tenancy keeps the central DB connection
     * accessible via the Tenant model (which extends BaseTenant that always uses the
     * central connection), so this cross-DB query is safe.
     *
     * @return Collection<int, Tenant>
     */
    public function getAvailableTenantsProperty(): Collection
    {
        $user = Auth::user();

        if (!tenancy()->initialized || !$user || !$user->isAdminOrAbove()) {
            return collect();
        }

        try {
            return Tenant::with('domains')
                ->where('status', TenantStatus::ACTIVE)
                ->orderBy('organization_name')
                ->get();
        } catch (\Exception) {
            return collect();
        }
    }

    public function render(): \Illuminate\View\View
    {
        return view('tenant.components.workspace.workspace-switcher', [
            // Current tenant from the tenancy bootstrapper — always populated on
            // tenant domains. Pass null-safely so the blade @if($currentTenant) guard works.
            'currentTenant'    => tenancy()->initialized ? tenancy()->tenant : null,
            'availableTenants' => $this->availableTenants,
            'wsTypeColors'     => $this->wsTypeColors,
        ]);
    }
}
