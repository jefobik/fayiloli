@extends('layouts.central')

@section('title', 'Tenant Management')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Header ────────────────────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 fw-semibold">
                <i class="fa-solid fa-building-user me-2 text-primary" aria-hidden="true"></i>
                Tenant Management
            </h1>
            <p class="text-muted small mb-0">Manage organisations and their provisioned databases</p>
        </div>
        @can('create', App\Models\Tenant::class)
        <a href="{{ route('tenants.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1" aria-hidden="true"></i> New Tenant
        </a>
        @endcan
    </div>

    {{-- ── Stats Row ──────────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="h2 fw-bold text-primary mb-0">{{ $stats['total'] }}</div>
                <div class="text-muted small">Total</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="h2 fw-bold text-success mb-0">{{ $stats['active'] }}</div>
                <div class="text-muted small">Active</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="h2 fw-bold text-warning mb-0">{{ $stats['pending'] }}</div>
                <div class="text-muted small">Pending</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="h2 fw-bold text-danger mb-0">{{ $stats['suspended'] }}</div>
                <div class="text-muted small">Suspended</div>
            </div>
        </div>
    </div>

    {{-- ── Table ──────────────────────────────────────────────────────────── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle" aria-label="Tenant list">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">Organisation</th>
                            <th scope="col">Admin Email</th>
                            <th scope="col">Type</th>
                            <th scope="col">Domains</th>
                            <th scope="col">Status</th>
                            <th scope="col">Created</th>
                            <th scope="col" class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tenants as $tenant)
                        @php $isPending = $tenant->status === \App\Enums\TenantStatus::PENDING; @endphp
                        <tr class="{{ $isPending ? 'table-warning' : '' }}">
                            <td class="ps-4 fw-semibold" data-label="Organisation">
                                <a href="{{ route('tenants.show', $tenant) }}" class="text-decoration-none text-dark">
                                    {{ $tenant->organization_name ?? '—' }}
                                </a>
                                <div class="text-muted small font-monospace">{{ $tenant->id }}</div>
                            </td>
                            <td class="small" data-label="Admin Email">{{ $tenant->admin_email ?? '—' }}</td>
                            <td data-label="Type">
                                <span class="badge rounded-pill {{ $tenant->plan_badge }}">
                                    {{ $tenant->plan_label }}
                                </span>
                            </td>
                            <td data-label="Domains">
                                @forelse ($tenant->domains as $domain)
                                    <span class="badge bg-light text-secondary border me-1 small">{{ $domain->domain }}</span>
                                @empty
                                    <span class="text-muted small">None</span>
                                @endforelse
                            </td>
                            <td data-label="Status">
                                @if ($isPending)
                                    <span class="badge {{ $tenant->status_badge }}"
                                          title="Provisioning in progress — activate or reject when ready">
                                        <span class="spinner-grow spinner-grow-sm me-1"
                                              style="width:.5rem;height:.5rem;vertical-align:.1em"
                                              role="status" aria-hidden="true"></span>
                                        {{ $tenant->status_label }}
                                    </span>
                                @else
                                    <span class="badge {{ $tenant->status_badge }}">
                                        <i class="fa-solid fa-{{ $tenant->status_icon }} me-1" aria-hidden="true"></i>
                                        {{ $tenant->status_label }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-muted small" data-label="Created">
                                {{ $tenant->created_at->format('d M Y') }}
                            </td>
                            <td class="text-end pe-4" data-label="Actions">
                                <div class="d-flex justify-content-end gap-1">
                                    {{-- View — always available --}}
                                    <a href="{{ route('tenants.show', $tenant) }}"
                                       class="btn btn-sm btn-outline-secondary"
                                       title="View {{ $tenant->organization_name }}">
                                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                    </a>

                                    {{-- Edit — locked while provisioning is pending --}}
                                    @if ($isPending)
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                disabled
                                                title="Provisioning in progress — edit once the tenant is activated"
                                                aria-disabled="true">
                                            <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                        </button>
                                    @else
                                        <a href="{{ route('tenants.edit', $tenant) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Edit {{ $tenant->organization_name }}">
                                            <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                        </a>
                                    @endif

                                    {{-- Status transition — shown for all statuses that have allowed transitions --}}
                                    @if ($tenant->status && count($tenant->status->allowedTransitions()))
                                        @php $first = $tenant->status->allowedTransitions()[0]; @endphp
                                        <button type="button"
                                                class="btn btn-sm {{ $first['btnClass'] }}"
                                                title="{{ $first['action'] }} {{ $tenant->organization_name }}"
                                                data-target-status="{{ $first['target']->value }}"
                                                data-action-label="{{ $first['action'] }}"
                                                data-tenant-name="{{ $tenant->organization_name }}"
                                                data-transition-url="{{ route('tenants.transition_status', $tenant) }}"
                                                onclick="openTransitionModal(this)">
                                            <i class="fa-solid fa-arrow-right-arrow-left" aria-hidden="true"></i>
                                        </button>
                                    @endif

                                    {{-- Delete — super-admin only; not shown while provisioning is pending --}}
                                    @can('delete', $tenant)
                                        @if ($isPending)
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    disabled
                                                    title="Provisioning in progress — cannot delete until activated or rejected"
                                                    aria-disabled="true">
                                                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                            </button>
                                        @else
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Delete {{ $tenant->organization_name }}"
                                                    data-tenant-name="{{ $tenant->organization_name }}"
                                                    data-delete-url="{{ route('tenants.destroy', $tenant) }}"
                                                    onclick="confirmDeleteTenant(this)">
                                                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                            </button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fa-solid fa-building-circle-exclamation fa-2x mb-2 d-block" aria-hidden="true"></i>
                                No tenants yet.
                                @can('create', App\Models\Tenant::class)
                                    <a href="{{ route('tenants.create') }}">Create the first one.</a>
                                @endcan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($tenants->hasPages())
        <div class="card-footer bg-transparent border-0">
            {{ $tenants->links() }}
        </div>
        @endif
    </div>
</div>

{{-- ── Status Transition Modal ─────────────────────────────────────────────── --}}
<div class="modal fade" id="transitionModal" tabindex="-1"
     aria-labelledby="transitionModalLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="transitionModalLabel">
                    <i class="fa-solid fa-arrow-right-arrow-left me-2 text-info" aria-hidden="true"></i>
                    <span id="transitionModalAction"></span> Tenant
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cancel"></button>
            </div>
            <form id="transitionForm" method="POST">
                @csrf @method('PATCH')
                <input type="hidden" name="status" id="transitionTargetStatus">
                <div class="modal-body pt-2">
                    <p class="mb-3 small">
                        Confirm: <strong id="transitionActionVerb"></strong>
                        <strong id="transitionTenantName"></strong>.
                    </p>
                    <div>
                        <label for="transitionReason" class="form-label small fw-semibold">
                            Reason <span class="text-muted fw-normal">(optional)</span>
                        </label>
                        <textarea name="reason" id="transitionReason" rows="2"
                                  class="form-control form-control-sm"
                                  placeholder="Brief explanation for audit trail…"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm" id="transitionSubmitBtn">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Delete Confirmation Modal (super-admin only) ───────────────────────── --}}
{{-- Rendered for all admins but only reachable via @can-gated buttons above --}}
<div class="modal fade" id="deleteTenantModal" tabindex="-1"
     aria-labelledby="deleteTenantModalLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-danger fw-semibold" id="deleteTenantModalLabel">
                    <i class="fa-solid fa-triangle-exclamation me-2" aria-hidden="true"></i>
                    Permanently Delete Tenant
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cancel deletion"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-1">You are about to permanently delete:</p>
                <p class="fw-semibold fs-6" id="deleteTenantName"></p>
                <p class="text-danger small mb-2">
                    This will <strong>drop the tenant's PostgreSQL database</strong>, destroy all
                    EDMS data, and remove all domain registrations.
                    <strong>This action is irrecoverable.</strong>
                </p>
                <p class="small mb-1 fw-semibold">Type the organisation name to confirm:</p>
                <input type="text" id="deleteConfirmInput"
                       class="form-control form-control-sm"
                       placeholder="Organisation name…"
                       autocomplete="off">
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteTenantForm" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" id="deleteConfirmBtn" class="btn btn-danger" disabled>
                        <i class="fa-solid fa-trash-can me-1" aria-hidden="true"></i> Delete Permanently
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDeleteTenant(btn) {
    var name = btn.getAttribute('data-tenant-name');
    document.getElementById('deleteTenantName').textContent = name;
    document.getElementById('deleteTenantForm').action = btn.getAttribute('data-delete-url');
    var input  = document.getElementById('deleteConfirmInput');
    var submit = document.getElementById('deleteConfirmBtn');
    input.value = '';
    submit.disabled = true;
    input.oninput = function () {
        submit.disabled = input.value.trim() !== name;
    };
    new bootstrap.Modal(document.getElementById('deleteTenantModal')).show();
    // Focus the input after the modal animates in.
    document.getElementById('deleteTenantModal').addEventListener('shown.bs.modal', function handler() {
        input.focus();
        this.removeEventListener('shown.bs.modal', handler);
    });
}
function openTransitionModal(btn) {
    var status = btn.getAttribute('data-target-status');
    var action = btn.getAttribute('data-action-label');
    var name   = btn.getAttribute('data-tenant-name');
    var url    = btn.getAttribute('data-transition-url');
    var submit = document.getElementById('transitionSubmitBtn');
    var match  = btn.className.match(/btn-(success|warning|danger|secondary)/);
    submit.className = 'btn btn-sm btn-' + (match ? match[1] : 'primary');
    submit.textContent = action;
    document.getElementById('transitionModalAction').textContent = action;
    document.getElementById('transitionActionVerb').textContent = action.toLowerCase();
    document.getElementById('transitionTenantName').textContent = name;
    document.getElementById('transitionTargetStatus').value = status;
    document.getElementById('transitionForm').action = url;
    document.getElementById('transitionReason').value = '';
    new bootstrap.Modal(document.getElementById('transitionModal')).show();
}
</script>
@endpush
