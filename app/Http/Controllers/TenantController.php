<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * TenantController — Central Administration
 *
 * Provides full CRUD management of tenants from the central (super-admin)
 * domain.  All operations run against the central PostgreSQL database; the
 * stancl/tenancy event pipeline handles tenant-database provisioning
 * (CreateDatabase → MigrateDatabase) automatically on creation and
 * database teardown on deletion.
 */
class TenantController extends Controller
{
    // ── List ─────────────────────────────────────────────────────────────────

    public function index(): View
    {
        $tenants = Tenant::with('domains')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('tenants.index', compact('tenants'));
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create(): View
    {
        return view('tenants.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'admin_email'       => ['required', 'email', 'max:255'],
            'plan'              => ['required', 'in:government,agency,department,secretariat,unit'],
            'domain'            => ['required', 'string', 'max:255',
                                    'regex:/^[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?(\.[a-z0-9-]{2,})+$/',
                                    'unique:domains,domain'],
        ]);

        // stancl/tenancy auto-generates a UUID for the tenant id.
        $tenant = Tenant::create([
            'organization_name' => $validated['organization_name'],
            'admin_email'       => $validated['admin_email'],
            'plan'              => $validated['plan'],
            'is_active'         => true,
        ]);

        // Attach the primary domain — triggers domain events.
        $tenant->domains()->create(['domain' => $validated['domain']]);

        return redirect()
            ->route('tenants.show', $tenant)
            ->with('success', 'Tenant "' . $tenant->organization_name . '" provisioned successfully.');
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(Tenant $tenant): View
    {
        $tenant->load('domains');

        return view('tenants.show', compact('tenant'));
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function edit(Tenant $tenant): View
    {
        $tenant->load('domains');

        return view('tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'admin_email'       => ['required', 'email', 'max:255'],
            'plan'              => ['required', 'in:government,agency,department,secretariat,unit'],
            'is_active'         => ['boolean'],
        ]);

        $tenant->update($validated);

        return redirect()
            ->route('tenants.show', $tenant)
            ->with('success', 'Tenant updated successfully.');
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $name = $tenant->organization_name;

        // Cascades to domain deletion via FK; the TenancyServiceProvider
        // TenantDeleted event fires the DeleteDatabase job automatically.
        $tenant->delete();

        return redirect()
            ->route('tenants.index')
            ->with('success', 'Tenant "' . $name . '" and its database have been removed.');
    }

    // ── Domain Management ─────────────────────────────────────────────────────

    public function addDomain(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255',
                         'regex:/^[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?(\.[a-z0-9-]{2,})+$/',
                         'unique:domains,domain'],
        ]);

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

        $tenant->domains()->where('id', $validated['domain_id'])->delete();

        return redirect()
            ->route('tenants.show', $tenant)
            ->with('success', 'Domain removed.');
    }

    // ── Toggle Active Status ──────────────────────────────────────────────────

    public function toggleActive(Tenant $tenant): RedirectResponse
    {
        $tenant->update(['is_active' => ! $tenant->is_active]);

        $state = $tenant->is_active ? 'activated' : 'suspended';

        return redirect()
            ->route('tenants.show', $tenant)
            ->with('success', "Tenant {$state} successfully.");
    }
}
