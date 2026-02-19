@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- ── Breadcrumb ─────────────────────────────────────────────────────── --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('tenants.index') }}">Tenants</a></li>
            <li class="breadcrumb-item active">{{ $tenant->organization_name }}</li>
        </ol>
    </nav>

    {{-- ── Flash ─────────────────────────────────────────────────────────── --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- ── Tenant Info Card ───────────────────────────────────────────── --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fa-solid fa-building-user me-2 text-primary"></i>
                        Tenant Details
                    </h6>
                    <div class="d-flex gap-2">
                        <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                        </a>
                        <form method="POST" action="{{ route('tenants.toggle_active', $tenant) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="btn btn-sm {{ $tenant->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                <i class="fa-solid {{ $tenant->is_active ? 'fa-pause' : 'fa-play' }} me-1"></i>
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

                        <dt class="col-sm-5 text-muted">Plan</dt>
                        <dd class="col-sm-7">
                            <span class="badge rounded-pill {{ $tenant->plan_badge }}">
                                {{ ucfirst($tenant->plan) }}
                            </span>
                        </dd>

                        <dt class="col-sm-5 text-muted">Status</dt>
                        <dd class="col-sm-7">
                            @if ($tenant->is_active)
                                <span class="badge bg-success-subtle text-success">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">Suspended</span>
                            @endif
                        </dd>

                        <dt class="col-sm-5 text-muted">Database</dt>
                        <dd class="col-sm-7 font-monospace small text-secondary">
                            tenant{{ $tenant->id }}
                        </dd>

                        <dt class="col-sm-5 text-muted">Created</dt>
                        <dd class="col-sm-7">{{ $tenant->created_at->format('d M Y, H:i') }}</dd>

                        <dt class="col-sm-5 text-muted">Last Updated</dt>
                        <dd class="col-sm-7">{{ $tenant->updated_at->format('d M Y, H:i') }}</dd>
                    </dl>
                </div>
                <div class="card-footer bg-white border-top pt-3">
                    <form method="POST" action="{{ route('tenants.destroy', $tenant) }}"
                          onsubmit="return confirm('Permanently delete this tenant and drop its database?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fa-solid fa-trash-can me-1"></i> Delete Tenant
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Domain Management Card ─────────────────────────────────────── --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fa-solid fa-globe me-2 text-info"></i>
                        Domains
                    </h6>
                </div>
                <div class="card-body">
                    {{-- Existing domains --}}
                    @forelse ($tenant->domains as $domain)
                        <div class="d-flex align-items-center justify-content-between border rounded px-3 py-2 mb-2">
                            <span class="font-monospace small">{{ $domain->domain }}</span>
                            <form method="POST" action="{{ route('tenants.domains.remove', $tenant) }}"
                                  onsubmit="return confirm('Remove domain {{ $domain->domain }}?')">
                                @csrf @method('DELETE')
                                <input type="hidden" name="domain_id" value="{{ $domain->id }}">
                                <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Remove">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-muted small">No domains assigned yet.</p>
                    @endforelse

                    {{-- Add domain form --}}
                    <form method="POST" action="{{ route('tenants.domains.add', $tenant) }}" class="mt-3">
                        @csrf
                        <label class="form-label small fw-semibold">Add Domain</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-plus"></i></span>
                            <input type="text" name="domain"
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
@endsection
