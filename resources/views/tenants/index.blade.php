@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Header ────────────────────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 fw-semibold">
                <i class="fa-solid fa-building-user me-2 text-primary"></i>
                Tenant Management
            </h1>
            <p class="text-muted small mb-0">Manage organisations and their provisioned databases</p>
        </div>
        <a href="{{ route('tenants.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i> New Tenant
        </a>
    </div>

    {{-- ── Flash ─────────────────────────────────────────────────────────── --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── Stats Row ──────────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="h2 fw-bold text-primary mb-0">{{ $tenants->total() }}</div>
                <div class="text-muted small">Total Tenants</div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="h2 fw-bold text-success mb-0">{{ $tenants->where('is_active', true)->count() }}</div>
                <div class="text-muted small">Active</div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="h2 fw-bold text-danger mb-0">{{ $tenants->where('is_active', false)->count() }}</div>
                <div class="text-muted small">Suspended</div>
            </div>
        </div>
    </div>

    {{-- ── Table ──────────────────────────────────────────────────────────── --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Organisation</th>
                            <th>Admin Email</th>
                            <th>Plan</th>
                            <th>Domains</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tenants as $tenant)
                        <tr>
                            <td class="ps-4 fw-semibold">
                                <a href="{{ route('tenants.show', $tenant) }}" class="text-decoration-none text-dark">
                                    {{ $tenant->organization_name ?? '—' }}
                                </a>
                                <div class="text-muted small font-monospace">{{ $tenant->id }}</div>
                            </td>
                            <td>{{ $tenant->admin_email ?? '—' }}</td>
                            <td>
                                <span class="badge rounded-pill {{ $tenant->plan_badge }}">
                                    {{ ucfirst($tenant->plan ?? 'starter') }}
                                </span>
                            </td>
                            <td>
                                @forelse ($tenant->domains as $domain)
                                    <span class="badge bg-light text-secondary border me-1">{{ $domain->domain }}</span>
                                @empty
                                    <span class="text-muted small">None</span>
                                @endforelse
                            </td>
                            <td>
                                @if ($tenant->is_active)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Suspended</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $tenant->created_at->format('d M Y') }}</td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('tenants.show', $tenant) }}"
                                       class="btn btn-sm btn-outline-secondary" title="View">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('tenants.edit', $tenant) }}"
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form method="POST" action="{{ route('tenants.toggle_active', $tenant) }}"
                                          class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="btn btn-sm {{ $tenant->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                title="{{ $tenant->is_active ? 'Suspend' : 'Activate' }}">
                                            <i class="fa-solid {{ $tenant->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('tenants.destroy', $tenant) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Delete tenant and drop its database? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fa-solid fa-building-circle-exclamation fa-2x mb-2 d-block"></i>
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
@endsection
