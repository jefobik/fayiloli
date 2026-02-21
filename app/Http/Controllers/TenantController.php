<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TenantModule;
use App\Enums\TenantStatus;
use App\Enums\TenantType;
use App\Http\Requests\StoreTenantRequest;
use App\Http\Requests\TransitionTenantStatusRequest;
use App\Http\Requests\UpdateTenantRequest;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * TenantController — Central Administration
 *
 * Manages the full tenant lifecycle from the super-admin domain.
 * Status changes are gated by the TenantStatus state machine —
 * callers cannot set arbitrary status values; they must use the
 * permitted transitions defined in TenantStatus::allowedTransitions().
 *
 * Authorization is delegated to TenantPolicy via authorizeResource().
 */
class TenantController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Tenant::class, 'tenant');
    }

    // ── List ──────────────────────────────────────────────────────────────────

    public function index(): View
    {
        $tenants = Tenant::with('domains')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total'     => Tenant::count(),
            'active'    => Tenant::where('status', TenantStatus::ACTIVE)->count(),
            'pending'   => Tenant::where('status', TenantStatus::PENDING)->count(),
            'suspended' => Tenant::where('status', TenantStatus::SUSPENDED)->count(),
            'inactive'  => Tenant::where('status', TenantStatus::INACTIVE)->count(),
        ];

        return view('tenants.index', compact('tenants', 'stats'));
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create(): View
    {
        return view('tenants.create', [
            'tenantTypes'   => TenantType::cases(),
            'tenantModules' => TenantModule::cases(),
            'defaultModules'=> TenantModule::defaults(),
        ]);
    }

    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Module selection: use the submitted array or fall back to defaults.
        $modules = $data['modules'] ?? TenantModule::defaults();

        // New tenants start as PENDING (is_active = false via Observer).
        // After the database is synchronously provisioned, transition to ACTIVE.
        $tenant = Tenant::create([
            'organization_name' => $data['organization_name'],
            'admin_email'       => $data['admin_email'],
            'tenant_type'       => $data['tenant_type'],
            'status'            => TenantStatus::PENDING,
            'notes'             => $data['notes'] ?? null,
            'settings'          => ['modules' => $modules],
        ]);

        // Attach the primary domain — fires DomainCreated event.
        $tenant->domains()->create(['domain' => $data['domain']]);

        // Provisioning is synchronous (shouldBeQueued = false) so the tenant DB
        // is ready by this point — immediately activate the tenant.
        $tenant->transitionStatus(TenantStatus::ACTIVE);

        return redirect()
            ->route('tenants.show', $tenant)
            ->with('success', "Tenant \"{$tenant->organization_name}\" provisioned and activated.");
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(Tenant $tenant): View
    {
        $tenant->load('domains');

        return view('tenants.show', [
            'tenant'        => $tenant,
            'transitions'   => $tenant->status?->allowedTransitions() ?? [],
            'tenantModules' => TenantModule::cases(),
        ]);
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function edit(Tenant $tenant): View
    {
        $tenant->load('domains');

        return view('tenants.edit', [
            'tenant'        => $tenant,
            'tenantTypes'   => TenantType::cases(),
            'tenantModules' => TenantModule::cases(),
        ]);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validated();

        // Persist module selection into the settings JSONB array.
        // When modules key is absent (form submitted without any checkbox),
        // treat it as "no modules" — the admin deliberately disabled everything.
        $modules = $data['modules'] ?? [];

        // Merge modules into existing settings, preserving any other setting keys.
        $settings = array_merge($tenant->settings ?? [], ['modules' => $modules]);

        $tenant->update([
            'organization_name' => $data['organization_name'],
            'admin_email'       => $data['admin_email'],
            'tenant_type'       => $data['tenant_type'],
            'notes'             => $data['notes'] ?? null,
            'settings'          => $settings,
        ]);

        return redirect()
            ->route('tenants.show', $tenant)
            ->with('success', 'Tenant details updated.');
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $name = $tenant->organization_name;

        // TenantDeleted event fires DeleteDatabase job automatically.
        $tenant->delete();

        return redirect()
            ->route('tenants.index')
            ->with('success', "Tenant \"{$name}\" and its database have been permanently removed.");
    }

    // ── Status Transition ─────────────────────────────────────────────────────

    /**
     * Apply a validated status transition through the state machine.
     *
     * TransitionTenantStatusRequest validates that the target state is
     * reachable from the tenant's current state before the controller
     * executes, so transitionStatus() here will never receive an illegal move.
     */
    public function transitionStatus(TransitionTenantStatusRequest $request, Tenant $tenant): RedirectResponse
    {
        $this->authorize('transitionStatus', $tenant);

        $target = TenantStatus::from($request->validated()['status']);
        $reason = $request->validated()['reason'] ?? null;

        $tenant->transitionStatus($target, $reason);

        return redirect()
            ->route('tenants.show', $tenant)
            ->with('success', "Tenant {$target->incomingActionLabel()} successfully.");
    }

    // ── Domain Management ─────────────────────────────────────────────────────

    public function addDomain(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'domain' => [
                'required', 'string', 'max:255',
                'regex:/^[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?(\.[a-z0-9\-]{2,})+$/',
                'unique:domains,domain',
            ],
        ]);

        $this->authorize('addDomain', $tenant);

        $tenant->domains()->create(['domain' => $validated['domain']]);

        return redirect()
            ->route('tenants.show', $tenant)
            ->with('success', "Domain {$validated['domain']} added.");
    }

    public function removeDomain(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'domain_id' => ['required', 'integer', 'exists:domains,id'],
        ]);

        $this->authorize('removeDomain', $tenant);

        $tenant->domains()->where('id', $validated['domain_id'])->delete();

        return redirect()
            ->route('tenants.show', $tenant)
            ->with('success', 'Domain removed.');
    }
}
