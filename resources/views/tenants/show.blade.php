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
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fa-solid fa-building-user me-2 text-primary" aria-hidden="true"></i>
                        Tenant Details
                    </h6>
                    <div class="d-flex gap-2">
                        <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-pen-to-square me-1" aria-hidden="true"></i> Edit
                        </a>
                        <form method="POST" action="{{ route('tenants.toggle_active', $tenant) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="btn btn-sm {{ $tenant->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                    aria-label="{{ $tenant->is_active ? 'Suspend' : 'Activate' }} {{ $tenant->organization_name }}">
                                <i class="fa-solid {{ $tenant->is_active ? 'fa-pause' : 'fa-play' }} me-1" aria-hidden="true"></i>
                                {{ $tenant->is_active ? 'Suspend' : 'Activate' }}
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5 text-muted">ID</dt>
                        <dd class="col-sm-7 font-monospace small">{{ $tenant->id }}</dd>

                        <dt class="col-sm-5 text-muted">Organisation</dt>
                        <dd class="col-sm-7 fw-semibold">{{ $tenant->organization_name }}</dd>

                        <dt class="col-sm-5 text-muted">Admin Email</dt>
                        <dd class="col-sm-7">{{ $tenant->admin_email }}</dd>

                        <dt class="col-sm-5 text-muted">Tenant Type</dt>
                        <dd class="col-sm-7">
                            <span class="badge rounded-pill {{ $tenant->plan_badge }}">
                                {{ ucfirst($tenant->tenant_type ?? $tenant->plan ?? '—') }}
                            </span>
                        </dd>

                        <dt class="col-sm-5 text-muted">Operational Status</dt>
                        <dd class="col-sm-7">
                            <span class="badge {{ $tenant->status_badge }}">
                                {{ ucfirst($tenant->status ?? '—') }}
                            </span>
                        </dd>

                        <dt class="col-sm-5 text-muted">Access</dt>
                        <dd class="col-sm-7">
                            @if ($tenant->is_active)
                                <span class="badge bg-success">
                                    <i class="fa-solid fa-circle-check me-1" aria-hidden="true"></i>Allowed
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="fa-solid fa-lock me-1" aria-hidden="true"></i>Blocked
                                </span>
                            @endif
                        </dd>

                        <dt class="col-sm-5 text-muted">Database</dt>
                        <dd class="col-sm-7 font-monospace small text-secondary">
                            tenant{{ $tenant->id }}
                        </dd>

                        @if ($tenant->notes)
                        <dt class="col-sm-5 text-muted">Notes</dt>
                        <dd class="col-sm-7 text-secondary small">{{ $tenant->notes }}</dd>
                        @endif

                        @if ($tenant->level > 0 || $tenant->parent_uuid)
                        <dt class="col-sm-5 text-muted">Hierarchy Level</dt>
                        <dd class="col-sm-7">{{ $tenant->level ?? 0 }}</dd>

                        @if ($tenant->parent_uuid)
                        <dt class="col-sm-5 text-muted">Parent ID</dt>
                        <dd class="col-sm-7 font-monospace small text-secondary">{{ $tenant->parent_uuid }}</dd>
                        @endif

                        @if ($tenant->hierarchy_path)
                        <dt class="col-sm-5 text-muted">Hierarchy Path</dt>
                        <dd class="col-sm-7 font-monospace small text-secondary" style="word-break:break-all">{{ $tenant->hierarchy_path }}</dd>
                        @endif
                        @endif

                        <dt class="col-sm-5 text-muted">Created</dt>
                        <dd class="col-sm-7">{{ $tenant->created_at->format('d M Y, H:i') }}</dd>

                        <dt class="col-sm-5 text-muted">Last Updated</dt>
                        <dd class="col-sm-7">{{ $tenant->updated_at->format('d M Y, H:i') }}</dd>
                    </dl>
                </div>
                <div class="card-footer bg-white border-top pt-3">
                    <button type="button"
                            class="btn btn-sm btn-danger"
                            data-tenant-name="{{ $tenant->organization_name }}"
                            data-delete-url="{{ route('tenants.destroy', $tenant) }}"
                            onclick="confirmDeleteTenant(this)">
                        <i class="fa-solid fa-trash-can me-1" aria-hidden="true"></i> Delete Tenant
                    </button>
                </div>
            </div>
        </div>

        {{-- ── Domain Management Card ─────────────────────────────────────── --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fa-solid fa-globe me-2 text-info" aria-hidden="true"></i>
                        Domains
                    </h6>
                </div>
                <div class="card-body">
                    {{-- Existing domains --}}
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
                        <p class="text-muted small">No domains assigned yet.</p>
                    @endforelse

                    {{-- Add domain form --}}
                    <form method="POST" action="{{ route('tenants.domains.add', $tenant) }}" class="mt-3">
                        @csrf
                        <label class="form-label small fw-semibold" for="domainInput">Add Domain</label>
                        <div class="input-group">
                            <span class="input-group-text" aria-hidden="true"><i class="fa-solid fa-plus" aria-hidden="true"></i></span>
                            <input type="text" name="domain" id="domainInput"
                                   class="form-control form-control-sm @error('domain') is-invalid @enderror"
                                   placeholder="subdomain.youredms.com"
                                   value="{{ old('domain') }}"
                                   aria-describedby="{{ $errors->has('domain') ? 'domainError' : '' }}">
                            <button type="submit" class="btn btn-sm btn-primary">Add</button>
                            @error('domain')
                                <div class="invalid-feedback" id="domainError" role="alert">{{ $message }}</div>
                            @enderror
                        </div>
                    </form>
                </div>
            </div>
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
function confirmDeleteTenant(btn) {
    document.getElementById('deleteTenantName').textContent = btn.getAttribute('data-tenant-name');
    document.getElementById('deleteTenantForm').action = btn.getAttribute('data-delete-url');
    new bootstrap.Modal(document.getElementById('deleteTenantModal')).show();
}
function confirmRemoveDomain(btn) {
    document.getElementById('removeDomainLabel').textContent = btn.getAttribute('data-domain-label');
    document.getElementById('removeDomainForm').action = btn.getAttribute('data-remove-url');
    document.getElementById('removeDomainId').value = btn.getAttribute('data-domain-id');
    new bootstrap.Modal(document.getElementById('removeDomainModal')).show();
}
</script>
@endpush
