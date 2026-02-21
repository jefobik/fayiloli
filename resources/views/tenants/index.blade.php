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
        <a href="{{ route('tenants.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1" aria-hidden="true"></i> New Tenant
        </a>
    </div>

    {{-- ── Stats Row ──────────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="h2 fw-bold text-primary mb-0" aria-label="{{ $tenants->total() }} total tenants">{{ $tenants->total() }}</div>
                <div class="text-muted small" aria-hidden="true">Total Tenants</div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="h2 fw-bold text-success mb-0" aria-label="{{ $tenants->where('is_active', true)->count() }} active tenants">{{ $tenants->where('is_active', true)->count() }}</div>
                <div class="text-muted small" aria-hidden="true">Active</div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="h2 fw-bold text-danger mb-0" aria-label="{{ $tenants->where('is_active', false)->count() }} suspended tenants">{{ $tenants->where('is_active', false)->count() }}</div>
                <div class="text-muted small" aria-hidden="true">Suspended</div>
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
                            <th scope="col">Tenant Type</th>
                            <th scope="col">Domains</th>
                            <th scope="col">Status</th>
                            <th scope="col">Created</th>
                            <th scope="col" class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tenants as $tenant)
                        <tr>
                            <td class="ps-4 fw-semibold" data-label="Organisation">
                                <a href="{{ route('tenants.show', $tenant) }}" class="text-decoration-none text-dark">
                                    {{ $tenant->organization_name ?? '—' }}
                                </a>
                                <div class="text-muted small font-monospace">{{ $tenant->id }}</div>
                            </td>
                            <td data-label="Admin Email">{{ $tenant->admin_email ?? '—' }}</td>
                            <td data-label="Tenant Type">
                                <span class="badge rounded-pill {{ $tenant->plan_badge }}">
                                    {{ ucfirst($tenant->tenant_type ?? $tenant->plan ?? '—') }}
                                </span>
                            </td>
                            <td data-label="Domains">
                                @forelse ($tenant->domains as $domain)
                                    <span class="badge bg-light text-secondary border me-1">{{ $domain->domain }}</span>
                                @empty
                                    <span class="text-muted small">None</span>
                                @endforelse
                            </td>
                            <td data-label="Status">
                                <span class="badge {{ $tenant->status_badge }}">
                                    {{ ucfirst($tenant->status ?? ($tenant->is_active ? 'active' : 'suspended')) }}
                                </span>
                                @if (!$tenant->is_active)
                                    <span class="badge bg-dark ms-1" title="Access blocked">
                                        <i class="fa-solid fa-lock fa-xs" aria-hidden="true"></i>
                                    </span>
                                @endif
                            </td>
                            <td class="text-muted small" data-label="Created">{{ $tenant->created_at->format('d M Y') }}</td>
                            <td class="text-end pe-4" data-label="Actions">
                                <div class="d-flex justify-content-end gap-1" role="group" aria-label="Actions for {{ $tenant->organization_name }}">
                                    <a href="{{ route('tenants.show', $tenant) }}"
                                       class="btn btn-sm btn-outline-secondary"
                                       aria-label="View {{ $tenant->organization_name }}">
                                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                    </a>
                                    <a href="{{ route('tenants.edit', $tenant) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       aria-label="Edit {{ $tenant->organization_name }}">
                                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                    </a>
                                    <form method="POST" action="{{ route('tenants.toggle_active', $tenant) }}"
                                          class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="btn btn-sm {{ $tenant->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                aria-label="{{ $tenant->is_active ? 'Suspend' : 'Activate' }} {{ $tenant->organization_name }}">
                                            <i class="fa-solid {{ $tenant->is_active ? 'fa-pause' : 'fa-play' }}" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            aria-label="Delete {{ $tenant->organization_name }}"
                                            data-tenant-name="{{ $tenant->organization_name }}"
                                            data-delete-url="{{ route('tenants.destroy', $tenant) }}"
                                            onclick="confirmDeleteTenant(this)">
                                        <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fa-solid fa-building-circle-exclamation fa-2x mb-2 d-block" aria-hidden="true"></i>
                                No tenants yet.
                                <a href="{{ route('tenants.create') }}">Create the first one.</a>
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

{{-- ── Delete Confirmation Modal ───────────────────────────────────────────── --}}
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
@endsection

@push('scripts')
<script>
function confirmDeleteTenant(btn) {
    var name = btn.getAttribute('data-tenant-name');
    var url  = btn.getAttribute('data-delete-url');
    document.getElementById('deleteTenantName').textContent = name;
    document.getElementById('deleteTenantForm').action = url;
    var modal = new bootstrap.Modal(document.getElementById('deleteTenantModal'));
    modal.show();
}
</script>
@endpush
