@extends('layouts.app')
@section('content')
    @php $rolePermissions = old('permissions', []); @endphp

    <div class="container py-4" style="max-width:860px">

        {{-- ── Breadcrumb ──────────────────────────────────────────────────────── --}}
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
                <li class="breadcrumb-item active" aria-current="page">New Role</li>
            </ol>
        </nav>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3">
                <h5 class="fw-semibold mb-0">
                    <i class="fas fa-shield-halved me-2 text-primary" aria-hidden="true"></i>Create Custom Role
                </h5>
                <p class="text-muted small mb-0 mt-1">Define a role name and select the permissions it grants.</p>
            </div>

            <form action="{{ route('roles.store') }}" method="POST" novalidate>
                @csrf

                <div class="card-body">

                    {{-- Role name --}}
                    <div class="mb-4">
                        <label for="role_name" class="form-label fw-semibold">
                            Role Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="role_name"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="e.g. auditor, reviewer, compliance_officer"
                               autocomplete="off"
                               required>
                        <div class="form-text text-muted small">
                            Lowercase letters, digits, hyphens, or underscores. Must be unique.
                        </div>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Permissions --}}
                    <div class="mb-2">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label fw-semibold mb-0">Permissions</label>
                            <button type="button" class="btn btn-sm btn-link text-muted p-0"
                                    onclick="document.querySelectorAll('.perm-check').forEach(c => { c.checked = !c.checked }); document.querySelectorAll('.group-toggle').forEach(t => { const g = t.dataset.group; const checks = document.querySelectorAll('.perm-check[data-group=\'' + g + '\']'); const done = [...checks].filter(c => c.checked).length; t.checked = done === checks.length; t.indeterminate = done > 0 && done < checks.length; })">
                                Toggle All
                            </button>
                        </div>
                        @include('roles.partials.permission_checkboxes', ['rolePermissions' => $rolePermissions])
                    </div>

                </div>

                <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1" aria-hidden="true"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-check me-1" aria-hidden="true"></i>Create Role
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var content = document.querySelector('.page-content');
            if (content) content.style.display = 'block';
            var overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.style.display = 'none';
        });
    </script>
@endsection
