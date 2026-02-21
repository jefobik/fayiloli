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
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * TenantController — Central Administration
 *
 * Manages the full tenant lifecycle from the super-admin domain.
 *
 * Provisioning idempotency contract
 * ──────────────────────────────────
 *  create() generates a UUID "provision key" stored in the session.
 *  store()  validates the key (via StoreTenantRequest::after()), then
 *           immediately CONSUMES it before calling Tenant::create().
 *  On success the key is gone — a browser-back + resubmit hits an empty
 *  session slot → StoreTenantRequest rejects it → the controller's
 *  secondary guard redirects the admin to the existing tenant with guidance.
 *
 * Status changes are gated by the TenantStatus state machine.
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
        // Generate a fresh idempotency key for this page load.
        // Stored in the session; embedded as a hidden field in the form so
        // StoreTenantRequest can verify it before provisioning executes.
        $provisionKey = (string) Str::uuid();
        session(['tenant_provision_key' => $provisionKey]);

        return view('tenants.create', [
            'tenantTypes'    => TenantType::cases(),
            'tenantModules'  => TenantModule::cases(),
            'defaultModules' => TenantModule::defaults(),
            'provisionKey'   => $provisionKey,
        ]);
    }

    public function store(StoreTenantRequest $request): RedirectResponse
    {
        // ── Belt-and-suspenders key check ─────────────────────────────────
        // StoreTenantRequest::after() already rejects mismatched keys at the
        // validation layer.  This guard covers the rare race where session
        // state changes between FormRequest evaluation and controller entry
        // (e.g. two concurrent tab submissions).
        $submittedKey = $request->input('_provision_key');
        $sessionKey   = session('tenant_provision_key');

        if (! $sessionKey || $submittedKey !== $sessionKey) {
            $existingTenant = $this->findTenantByDomain($request->input('domain', ''));

            return $existingTenant
                ? redirect()->route('tenants.show', $existingTenant)
                             ->with('warning', 'This tenant was already provisioned. You have been redirected to the existing record.')
                : redirect()->route('tenants.create')
                             ->with('warning', 'Your provisioning session expired. Please fill in the details again.')
                             ->withInput();
        }

        // ── Consume the key BEFORE any DB write ───────────────────────────
        // After this line the key is gone. Any subsequent submission (same
        // browser tab, parallel tab, or replayed request) will be rejected.
        session()->forget('tenant_provision_key');

        // ── Domain collision guard ─────────────────────────────────────────
        // The unique:domains,domain rule in StoreTenantRequest already catches
        // this at validation.  This guard converts a raw 422 into a friendly
        // redirect to the existing tenant record for a better admin UX.
        $data   = $request->validated();
        $domain = $data['domain'];

        $existingByDomain = \Stancl\Tenancy\Database\Models\Domain::where('domain', $domain)->first();

        if ($existingByDomain) {
            $existingTenant = Tenant::find($existingByDomain->tenant_id);

            return $existingTenant
                ? redirect()->route('tenants.show', $existingTenant)
                             ->with('warning', "Domain \"{$domain}\" is already registered to this tenant. You have been redirected to the existing record.")
                : redirect()->route('tenants.index')
                             ->with('warning', "Domain \"{$domain}\" is already registered.");
        }

        // ── Provision ─────────────────────────────────────────────────────
        $modules = $data['modules'] ?? TenantModule::defaults();

        $tenant = Tenant::create([
            'organization_name' => $data['organization_name'],
            'admin_email'       => $data['admin_email'],
            'tenant_type'       => $data['tenant_type'],
            'status'            => TenantStatus::PENDING,
            'notes'             => $data['notes'] ?? null,
            'settings'          => ['modules' => $modules],
        ]);

        // Attach the primary domain — fires DomainCreated event.
        $tenant->domains()->create(['domain' => $domain]);

        // Provisioning is synchronous (shouldBeQueued = false) so the tenant
        // DB is ready — immediately activate.
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
        $data     = $request->validated();
        $modules  = $data['modules'] ?? [];
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

    /**
     * Permanently delete a tenant and drop its database — super-admin only.
     *
     * Three independent guards must ALL pass before the database is touched:
     *
     *   Gate 1 — Route middleware 'super-admin' (EnsureSuperAdmin)
     *     Resolves before this controller is instantiated.  Non-super-admins
     *     receive a 403 at the routing layer, never reaching this method.
     *
     *   Gate 2 — authorizeResource() via TenantPolicy::delete()
     *     Gate::before() in AppServiceProvider returns true for super-admins,
     *     short-circuiting the policy.  Non-super-admins would reach
     *     TenantPolicy::delete() → false → 403.  Because Gate 1 already
     *     blocked them, Gate 2 is a defence-in-depth backstop.
     *
     *   Gate 3 — abort_unless() explicit check inside this method
     *     The final in-code guard.  Catches any edge case where a future
     *     middleware or policy refactor inadvertently widens access.  This
     *     check is intentionally duplicative — it is the last line of defence.
     *
     * Blast-radius note: deleting a tenant drops its PostgreSQL database,
     * all EDMS data, and all domain registrations.  This is irrecoverable.
     * The UI enforces a typed-name confirmation dialog before submission.
     */
    public function destroy(Tenant $tenant): RedirectResponse
    {
        // Gate 3 — final explicit super-admin assertion (belt-and-suspenders).
        abort_unless(
            auth()->user()?->isSuperAdmin(),
            403,
            'Only the platform super-administrator may delete tenants.'
        );

        $name = $tenant->organization_name;

        // Stancl/tenancy fires TenantDeleted → DeleteDatabase job.
        // All domains are cascade-deleted by the domains table FK.
        $tenant->delete();

        return redirect()
            ->route('tenants.index')
            ->with('success', "Tenant \"{$name}\" and its database have been permanently deleted.");
    }

    // ── Status Transition ─────────────────────────────────────────────────────

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

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Attempt to resolve a Tenant from a domain string.
     * Returns null when the domain is empty or has no registered record.
     */
    private function findTenantByDomain(string $domain): ?Tenant
    {
        if (! $domain) {
            return null;
        }

        $domainRecord = \Stancl\Tenancy\Database\Models\Domain::where('domain', $domain)->first();

        return $domainRecord ? Tenant::find($domainRecord->tenant_id) : null;
    }
}
