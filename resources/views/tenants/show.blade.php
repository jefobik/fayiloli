@extends('layouts.central')

@section('title', $tenant->organization_name)

@section('content')
<div class="container-fluid py-4">

    {{-- ── Breadcrumb ─────────────────────────────────────────────────────── --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('tenants.index') }}">Tenants</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $tenant->organization_name }}</li>
        </ol>
    </nav>

    <div class="row g-4">

        {{-- ── Tenant Info Card ───────────────────────────────────────────── --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fa-solid fa-building-user me-2 text-primary" aria-hidden="true"></i>
                        Tenant Details
                    </h6>
                    <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fa-solid fa-pen-to-square me-1" aria-hidden="true"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-muted small">ID</dt>
                        <dd class="col-sm-7 font-monospace small text-secondary">{{ $tenant->id }}</dd>

                        <dt class="col-sm-5 text-muted small">Organisation</dt>
                        <dd class="col-sm-7 fw-semibold">{{ $tenant->organization_name }}</dd>

                        <dt class="col-sm-5 text-muted small">Admin Email</dt>
                        <dd class="col-sm-7 small">{{ $tenant->admin_email }}</dd>

                        <dt class="col-sm-5 text-muted small">Tenant Type</dt>
                        <dd class="col-sm-7">
                            <span class="badge rounded-pill {{ $tenant->plan_badge }}">
                                {{ $tenant->plan_label }}
                            </span>
                        </dd>

                        <dt class="col-sm-5 text-muted small">Database</dt>
                        <dd class="col-sm-7 font-monospace small text-secondary">tenant{{ $tenant->id }}</dd>

                        @if ($tenant->notes)
                        <dt class="col-sm-5 text-muted small">Notes</dt>
                        <dd class="col-sm-7 small text-secondary">{{ $tenant->notes }}</dd>
                        @endif

                        @if ($tenant->level > 0 || $tenant->parent_uuid)
                        <dt class="col-sm-5 text-muted small">Hierarchy Level</dt>
                        <dd class="col-sm-7 small">{{ $tenant->level ?? 0 }}</dd>
                        @if ($tenant->parent_uuid)
                        <dt class="col-sm-5 text-muted small">Parent ID</dt>
                        <dd class="col-sm-7 font-monospace small text-secondary">{{ $tenant->parent_uuid }}</dd>
                        @endif
                        @endif

                        <dt class="col-sm-5 text-muted small">Created</dt>
                        <dd class="col-sm-7 small">{{ $tenant->created_at->format('d M Y, H:i') }}</dd>

                        <dt class="col-sm-5 text-muted small">Last Updated</dt>
                        <dd class="col-sm-7 small">{{ $tenant->updated_at->format('d M Y, H:i') }}</dd>
                    </dl>
                </div>
                <div class="card-footer bg-white border-top pt-3">
                    <button type="button"
                            class="btn btn-sm btn-outline-danger"
                            data-tenant-name="{{ $tenant->organization_name }}"
                            data-delete-url="{{ route('tenants.destroy', $tenant) }}"
                            onclick="confirmDeleteTenant(this)"
                            aria-label="Delete {{ $tenant->organization_name }}">
                        <i class="fa-solid fa-trash-can me-1" aria-hidden="true"></i> Delete Tenant
                    </button>
                </div>
            </div>
        </div>

        {{-- ── Status & Lifecycle Card ─────────────────────────────────────── --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fa-solid fa-arrow-right-arrow-left me-2 text-info" aria-hidden="true"></i>
                        Lifecycle Status
                    </h6>
                </div>
                <div class="card-body">

                    {{-- Current status ---------------------------------------- --}}
                    <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded border bg-light">
                        <span class="badge {{ $tenant->status_badge }} fs-6 px-3 py-2">
                            <i class="fa-solid fa-{{ $tenant->status_icon }} me-1" aria-hidden="true"></i>
                            {{ $tenant->status_label }}
                        </span>
                        <p class="small text-muted mb-0">
                            @if ($tenant->isActive())
                                EDMS is <strong class="text-success">accessible</strong>.
                            @elseif ($tenant->isPending())
                                Awaiting activation by an administrator.
                            @elseif ($tenant->isSuspended())
                                All logins <strong class="text-danger">blocked</strong>.
                            @else
                                Tenant has been deactivated.
                            @endif
                        </p>
                    </div>

                    {{-- Available transitions ---------------------------------- --}}
                    @if (count($transitions))
                        <p class="text-muted small fw-semibold mb-2">Available Actions</p>
                        <div class="d-flex flex-column gap-2">
                            @foreach ($transitions as $t)
                            <button type="button"
                                    class="btn btn-sm {{ $t['btnClass'] }} text-start"
                                    data-target-status="{{ $t['target']->value }}"
                                    data-action-label="{{ $t['action'] }}"
                                    data-tenant-name="{{ $tenant->organization_name }}"
                                    data-transition-url="{{ route('tenants.transition_status', $tenant) }}"
                                    onclick="openTransitionModal(this)">
                                {{ $t['action'] }}
                            </button>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small mb-0">No further transitions available from this state.</p>
                    @endif

                    {{-- State machine legend ----------------------------------- --}}
                    <hr class="my-3">
                    <p class="text-muted small fw-semibold mb-2">State Machine</p>
                    <ul class="list-unstyled small text-muted mb-0" style="line-height:1.8">
                        <li><span class="badge bg-warning text-dark">Pending</span> &rarr; Activate / Reject</li>
                        <li><span class="badge bg-success">Active</span> &rarr; Suspend / Deactivate</li>
                        <li><span class="badge bg-danger">Suspended</span> &rarr; Reactivate / Archive</li>
                        <li><span class="badge bg-secondary">Inactive</span> &rarr; Reactivate</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- ── Workspace & Module Access Card ────────────────────────────── --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fa-solid fa-grid me-2 text-primary" aria-hidden="true"></i>
                        Workspace &amp; Modules
                    </h6>
                    <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                    </a>
                </div>
                <div class="card-body">

                    {{-- Quick-launch links --------------------------------- --}}
                    @if ($tenant->domains->isNotEmpty())
                        <p class="text-muted small fw-semibold mb-2">Launch Workspace</p>
                        <div class="d-flex flex-column gap-1 mb-3">
                            @foreach ($tenant->domains as $domain)
                                <a href="http://{{ $domain->domain }}"
                                   target="_blank" rel="noopener"
                                   class="btn btn-sm btn-outline-primary text-start">
                                    <i class="fa-solid fa-arrow-up-right-from-square me-1" aria-hidden="true"></i>
                                    {{ $domain->domain }}
                                </a>
                            @endforeach
                        </div>
                        <hr class="my-3">
                    @endif

                    {{-- Module status chips ------------------------------- --}}
                    <p class="text-muted small fw-semibold mb-2">Enabled Modules</p>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach ($tenantModules as $module)
                            @php $enabled = $tenant->hasModule($module); @endphp
                            <span class="badge rounded-pill {{ $enabled ? 'bg-success' : 'bg-light text-secondary border' }}"
                                  title="{{ $module->description() }}">
                                <i class="fa-solid fa-{{ $module->icon() }} me-1" aria-hidden="true"></i>
                                {{ $module->label() }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Domain Management Card (full-width second row) ──────────────── --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fa-solid fa-globe me-2 text-success" aria-hidden="true"></i>
                        Domains
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Existing domains list --}}
                        <div class="col-md-8">
                            @forelse ($tenant->domains as $domain)
                                <div class="d-flex align-items-center justify-content-between border rounded px-3 py-2 mb-2">
                                    <span class="font-monospace small">{{ $domain->domain }}</span>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger border-0"
                                            aria-label="Remove domain {{ $domain->domain }}"
                                            data-domain-label="{{ $domain->domain }}"
                                            data-remove-url="{{ route('tenants.domains.remove', $tenant) }}"
                                            data-domain-id="{{ $domain->id }}"
                                            onclick="confirmRemoveDomain(this)">
                                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                    </button>
                                </div>
                            @empty
                                <p class="text-muted small mb-0">No domains assigned yet.</p>
                            @endforelse
                        </div>
                        {{-- Add domain form --}}
                        <div class="col-md-4">
                            <form method="POST" action="{{ route('tenants.domains.add', $tenant) }}">
                                @csrf
                                <label class="form-label small fw-semibold" for="domainInput">Add Domain</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-plus" aria-hidden="true"></i></span>
                                    <input type="text" name="domain" id="domainInput"
                                           class="form-control form-control-sm @error('domain') is-invalid @enderror"
                                           placeholder="subdomain.youredms.com"
                                           value="{{ old('domain') }}">
                                    <button type="submit" class="btn btn-sm btn-primary">Add</button>
                                    @error('domain')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                            Reason <span class="text-muted fw-normal">(optional — saved to tenant notes)</span>
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

{{-- ── Delete Tenant Modal ─────────────────────────────────────────────────── --}}
<div class="modal fade" id="deleteTenantModal" tabindex="-1"
     aria-labelledby="deleteTenantModalLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-danger fw-semibold" id="deleteTenantModalLabel">
                    <i class="fa-solid fa-triangle-exclamation me-2" aria-hidden="true"></i>
                    Delete Tenant
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cancel deletion"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-1">You are about to permanently delete:</p>
                <p class="fw-semibold" id="deleteTenantName"></p>
                <p class="text-danger small mb-0">
                    This will drop the tenant's database and remove all associated data.
                    <strong>This action cannot be undone.</strong>
                </p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteTenantForm" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-trash-can me-1" aria-hidden="true"></i> Delete Permanently
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ── Remove Domain Modal ─────────────────────────────────────────────────── --}}
<div class="modal fade" id="removeDomainModal" tabindex="-1"
     aria-labelledby="removeDomainModalLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="removeDomainModalLabel">Remove Domain</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cancel"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-0 small">Remove <span class="fw-semibold font-monospace" id="removeDomainLabel"></span>?</p>
            </div>
            <div class="modal-footer border-0 pt-1">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="removeDomainForm" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <input type="hidden" name="domain_id" id="removeDomainId">
                    <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ── Status transition modal ────────────────────────────────────────────────
function openTransitionModal(btn) {
    var status = btn.getAttribute('data-target-status');
    var action = btn.getAttribute('data-action-label');
    var name   = btn.getAttribute('data-tenant-name');
    var url    = btn.getAttribute('data-transition-url');

    var submit = document.getElementById('transitionSubmitBtn');
    // Derive bootstrap btn class from the trigger button classes.
    var match = btn.className.match(/btn-(success|warning|danger|secondary)/);
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

// ── Delete confirmation modal ──────────────────────────────────────────────
function confirmDeleteTenant(btn) {
    document.getElementById('deleteTenantName').textContent = btn.getAttribute('data-tenant-name');
    document.getElementById('deleteTenantForm').action = btn.getAttribute('data-delete-url');
    new bootstrap.Modal(document.getElementById('deleteTenantModal')).show();
}

// ── Remove domain modal ────────────────────────────────────────────────────
function confirmRemoveDomain(btn) {
    document.getElementById('removeDomainLabel').textContent = btn.getAttribute('data-domain-label');
    document.getElementById('removeDomainForm').action = btn.getAttribute('data-remove-url');
    document.getElementById('removeDomainId').value = btn.getAttribute('data-domain-id');
    new bootstrap.Modal(document.getElementById('removeDomainModal')).show();
}
</script>
@endpush
