<?php

namespace App\Livewire\Layouts;

use Livewire\Component;

use App\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class WorkspaceSwitcher extends Component
{
    public array $wsTypeColors = [
        'government' => ['#dc2626', '#b91c1c'],
        'secretariat' => ['#4f46e5', '#4338ca'],
        'agency' => ['#0284c7', '#0369a1'],
        'department' => ['#16a34a', '#15803d'],
        'unit' => ['#d97706', '#b45309'],
    ];

    public function getAvailableTenantsProperty(): Collection
    {
        $currentTenant = tenancy()->tenant;
        $user = Auth::user();

        if (!$currentTenant || !$user || !$user->isAdminOrAbove()) {
            return collect();
        }

        try {
            return Tenant::with('domains')
                ->where('status', \App\Enums\TenantStatus::ACTIVE)
                ->where('id', '!=', $currentTenant->id)
                ->orderBy('organization_name')
                ->get();
        } catch (\Exception $e) {
            return collect();
        }
    }

    public function render()
    {
        return view('livewire.layouts.workspace-switcher', [
            'currentTenant' => tenancy()->tenant,
            'availableTenants' => $this->availableTenants,
        ]);
    }
}
