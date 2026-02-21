@extends('layouts.central')

@section('title', 'New Tenant')

@section('content')
<div class="container py-4" style="max-width:720px">

    {{-- ── Breadcrumb ─────────────────────────────────────────────────────── --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('tenants.index') }}">Tenants</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">New Tenant</li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-semibold">
                <i class="fa-solid fa-building-user me-2 text-primary" aria-hidden="true"></i>
                Provision New Tenant
            </h5>
            <p class="text-muted small mb-0 mt-1">
                A dedicated PostgreSQL database is created and migrated automatically.
                The tenant starts as <strong>Pending</strong> then transitions to <strong>Active</strong>
                once provisioning completes.
            </p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('tenants.store') }}" novalidate>
                @csrf

                {{-- Organisation Name --}}
                <div class="mb-3">
                    <label for="organization_name" class="form-label fw-semibold">
                        Organisation Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="organization_name" name="organization_name"
                           class="form-control @error('organization_name') is-invalid @enderror"
                           value="{{ old('organization_name') }}"
                           placeholder="Federal Ministry of Finance" required>
                    @error('organization_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Admin Email --}}
                <div class="mb-3">
                    <label for="admin_email" class="form-label fw-semibold">
                        Admin Email <span class="text-danger">*</span>
                    </label>
                    <input type="email" id="admin_email" name="admin_email"
                           class="form-control @error('admin_email') is-invalid @enderror"
                           value="{{ old('admin_email') }}"
                           placeholder="admin@finance.gov.ng" required>
                    @error('admin_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tenant Type --}}
                <div class="mb-3">
                    <label for="tenant_type" class="form-label fw-semibold">
                        Tenant Type <span class="text-danger">*</span>
                    </label>
                    <select id="tenant_type" name="tenant_type"
                            class="form-select @error('tenant_type') is-invalid @enderror" required>
                        <option value="">— Select a tenant type —</option>
                        @foreach ($tenantTypes as $type)
                            <option value="{{ $type->value }}"
                                    {{ old('tenant_type') === $type->value ? 'selected' : '' }}>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('tenant_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Primary Domain --}}
                <div class="mb-3">
                    <label for="domain" class="form-label fw-semibold">
                        Primary Domain <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text" aria-hidden="true">
                            <i class="fa-solid fa-globe" aria-hidden="true"></i>
                        </span>
                        <input type="text" id="domain" name="domain"
                               class="form-control @error('domain') is-invalid @enderror"
                               value="{{ old('domain') }}"
                               placeholder="finance.youredms.gov.ng" required>
                        @error('domain')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-text">
                        Fully-qualified hostname (e.g. <code>finance.youredms.gov.ng</code>).
                        The DNS A-record must point to this server before users can log in.
                    </div>
                </div>

                {{-- Notes --}}
                <div class="mb-4">
                    <label for="notes" class="form-label fw-semibold">Notes</label>
                    <textarea id="notes" name="notes" rows="2"
                              class="form-control @error('notes') is-invalid @enderror"
                              placeholder="Optional internal notes…">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ── Module Access ────────────────────────────────────────── --}}
                <div class="mb-4">
                    <hr class="mb-3">
                    <p class="fw-semibold mb-1">
                        <i class="fa-solid fa-grid me-1 text-muted" aria-hidden="true"></i>
                        Module Access
                    </p>
                    <p class="text-muted small mb-3">
                        Choose which EDMS features are available to this tenant's users.
                        Pre-checked items are the recommended defaults.
                    </p>
                    @error('modules')
                        <div class="alert alert-danger py-1 small mb-2">{{ $message }}</div>
                    @enderror
                    @error('modules.*')
                        <div class="alert alert-danger py-1 small mb-2">{{ $message }}</div>
                    @enderror
                    <div class="row g-2">
                        @foreach ($tenantModules as $module)
                            @php
                                $oldModules = old('modules');
                                $checked = $oldModules !== null
                                    ? in_array($module->value, $oldModules, true)
                                    : in_array($module->value, $defaultModules, true);
                            @endphp
                            <div class="col-sm-6">
                                <div class="form-check border rounded px-3 py-2 h-100 {{ $checked ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                                    <input class="form-check-input" type="checkbox"
                                           name="modules[]"
                                           id="module_{{ $module->value }}"
                                           value="{{ $module->value }}"
                                           {{ $checked ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="module_{{ $module->value }}">
                                        <span class="fw-semibold small">
                                            <i class="fa-solid fa-{{ $module->icon() }} me-1 text-muted" aria-hidden="true"></i>
                                            {{ $module->label() }}
                                        </span>
                                        <span class="d-block text-muted" style="font-size:.75rem; line-height:1.3">
                                            {{ $module->description() }}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa-solid fa-rocket me-1" aria-hidden="true"></i> Provision Tenant
                    </button>
                    <a href="{{ route('tenants.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
