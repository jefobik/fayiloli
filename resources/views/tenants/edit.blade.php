@extends('layouts.central')

@section('title', 'Edit Tenant — ' . $tenant->organization_name)

@section('content')
<div class="container py-4" style="max-width:720px">

    {{-- ── Breadcrumb ─────────────────────────────────────────────────────── --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('tenants.index') }}">Tenants</a></li>
            <li class="breadcrumb-item">
                <a href="{{ route('tenants.show', $tenant) }}">{{ $tenant->organization_name }}</a>
            </li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-semibold">
                <i class="fa-solid fa-pen-to-square me-2 text-primary" aria-hidden="true"></i>
                Edit Tenant Details
            </h5>
            <div class="text-muted small font-monospace mt-1">{{ $tenant->id }}</div>
        </div>
        <div class="card-body">

            {{-- ── Current status (read-only reminder) ── --}}
            <div class="alert alert-light border d-flex align-items-center gap-2 py-2 mb-4" role="note">
                <span class="badge {{ $tenant->status_badge }}">
                    <i class="fa-solid fa-{{ $tenant->status_icon }} me-1" aria-hidden="true"></i>
                    {{ $tenant->status_label }}
                </span>
                <span class="small text-muted">
                    To change the lifecycle status, use the
                    <a href="{{ route('tenants.show', $tenant) }}">Status panel</a> on the tenant detail page.
                </span>
            </div>

            <form method="POST" action="{{ route('tenants.update', $tenant) }}" novalidate>
                @csrf @method('PUT')

                {{-- Organisation Name --}}
                <div class="mb-3">
                    <label for="organization_name" class="form-label fw-semibold">
                        Organisation Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="organization_name" name="organization_name"
                           class="form-control @error('organization_name') is-invalid @enderror"
                           value="{{ old('organization_name', $tenant->organization_name) }}"
                           required>
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
                           value="{{ old('admin_email', $tenant->admin_email) }}"
                           required>
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
                        @foreach ($tenantTypes as $type)
                            <option value="{{ $type->value }}"
                                    {{ old('tenant_type', $tenant->tenant_type?->value ?? $tenant->tenant_type) === $type->value ? 'selected' : '' }}>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('tenant_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Notes --}}
                <div class="mb-4">
                    <label for="notes" class="form-label fw-semibold">Notes</label>
                    <textarea id="notes" name="notes" rows="3"
                              class="form-control @error('notes') is-invalid @enderror"
                              placeholder="Internal notes about this tenant…">{{ old('notes', $tenant->notes) }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Internal notes; also updated by status transition reasons.</div>
                </div>

                {{-- ── Module Access ────────────────────────────────────────── --}}
                <div class="mb-4">
                    <hr class="mb-3">
                    <p class="fw-semibold mb-1">
                        <i class="fa-solid fa-grid me-1 text-muted" aria-hidden="true"></i>
                        Module Access
                    </p>
                    <p class="text-muted small mb-3">
                        Enable or disable EDMS feature modules for this tenant.
                        Disabled modules return a 403 when users attempt to access them.
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
                                $oldModules  = old('modules');
                                $savedModules = $tenant->enabledModules();
                                $checked = $oldModules !== null
                                    ? in_array($module->value, $oldModules, true)
                                    : in_array($module->value, $savedModules, true);
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
                        <i class="fa-solid fa-floppy-disk me-1" aria-hidden="true"></i> Save Changes
                    </button>
                    <a href="{{ route('tenants.show', $tenant) }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
