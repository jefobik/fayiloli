@extends('layouts.app')
@section('content')
    <div class="container py-4">

        {{-- ── Header ──────────────────────────────────────────────────────────── --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h1 class="h4 fw-bold text-primary mb-0">
                    <i class="fas fa-shield-halved me-2" aria-hidden="true"></i>Roles &amp; Permissions
                </h1>
                <p class="text-muted small mb-0">Manage workspace roles and their permission sets</p>
            </div>
            @can('create', App\Models\Role::class)
                <a href="{{ route('roles.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1" aria-hidden="true"></i> New Role
                </a>
            @endcan
        </div>

        {{-- ── Stat cards ───────────────────────────────────────────────────────── --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white shrink-0"
                             style="width:40px;height:40px;background:linear-gradient(135deg,#4f46e5,#7c3aed)">
                            <i class="fas fa-shield-halved" style="font-size:1rem" aria-hidden="true"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:1.4rem;line-height:1">{{ $stats['total'] }}</div>
                            <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.04em">Total</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white shrink-0"
                             style="width:40px;height:40px;background:linear-gradient(135deg,#64748b,#475569)">
                            <i class="fas fa-lock" style="font-size:1rem" aria-hidden="true"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:1.4rem;line-height:1">{{ $stats['system'] }}</div>
                            <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.04em">System</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white shrink-0"
                             style="width:40px;height:40px;background:linear-gradient(135deg,#059669,#047857)">
                            <i class="fas fa-sliders" style="font-size:1rem" aria-hidden="true"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:1.4rem;line-height:1">{{ $stats['custom'] }}</div>
                            <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.04em">Custom</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white shrink-0"
                             style="width:40px;height:40px;background:linear-gradient(135deg,#0891b2,#0e7490)">
                            <i class="fas fa-users" style="font-size:1rem" aria-hidden="true"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size:1.4rem;line-height:1">{{ $stats['total_users'] }}</div>
                            <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:.04em">Assigned Users</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Roles table ─────────────────────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive rounded">
                    <table class="table table-hover align-middle mb-0" aria-label="Roles list">
                        <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200">
                            <tr>
                                <th scope="col" class="ps-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Role</th>
                                <th scope="col" class="py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Type</th>
                                <th scope="col" class="py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Permissions</th>
                                <th scope="col" class="py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-center">Users</th>
                                <th scope="col" class="pe-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $roleGradients = [
                                    'admin'   => 'linear-gradient(135deg,#7c3aed,#5b21b6)',
                                    'manager' => 'linear-gradient(135deg,#0891b2,#0e7490)',
                                    'user'    => 'linear-gradient(135deg,#059669,#047857)',
                                    'viewer'  => 'linear-gradient(135deg,#64748b,#475569)',
                                ];
                            @endphp
                            @forelse($roles as $role)
                                @php $avatarGradient = $roleGradients[$role->name] ?? 'linear-gradient(135deg,#4f46e5,#6d28d9)'; @endphp
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors
                                           border-b border-slate-100 dark:border-slate-800 last:border-0">

                                    {{-- Name --}}
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shrink-0"
                                                 style="width:34px;height:34px;font-size:0.75rem;background:{{ $avatarGradient }}"
                                                 aria-hidden="true">
                                                {{ strtoupper(substr($role->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('roles.show', $role) }}"
                                                   class="fw-semibold text-dark text-decoration-none hover:text-primary"
                                                   style="font-size:0.875rem">
                                                    {{ ucfirst($role->name) }}
                                                </a>
                                                <div class="text-muted" style="font-size:0.72rem">
                                                    guard: {{ $role->guard_name }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Type --}}
                                    <td class="py-3">
                                        @if($role->is_system)
                                            <span class="badge bg-light text-secondary border" style="font-size:0.7rem">
                                                <i class="fas fa-lock me-1" aria-hidden="true"></i>System
                                            </span>
                                        @else
                                            <span class="badge bg-light text-success border border-success" style="font-size:0.7rem">
                                                Custom
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Permissions count --}}
                                    <td class="py-3 text-center">
                                        <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold"
                                              style="font-size:0.8rem">
                                            {{ $role->permissions_count }}
                                        </span>
                                    </td>

                                    {{-- Users count --}}
                                    <td class="py-3 text-center">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary fw-semibold"
                                              style="font-size:0.8rem">
                                            {{ $role->users_count }}
                                        </span>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="pe-4 py-3 text-end">
                                        <div class="d-flex justify-content-end gap-1"
                                             role="group" aria-label="Actions for {{ $role->name }}">
                                            <a href="{{ route('roles.show', $role) }}"
                                               class="btn btn-sm btn-outline-secondary"
                                               title="View">
                                                <i class="fas fa-eye" aria-hidden="true"></i>
                                            </a>
                                            @can('update', $role)
                                                <a href="{{ route('roles.edit', $role) }}"
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="Edit permissions">
                                                    <i class="fas fa-pen" aria-hidden="true"></i>
                                                </a>
                                            @endcan
                                            @can('delete', $role)
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Delete"
                                                        data-role-name="{{ $role->name }}"
                                                        data-delete-url="{{ route('roles.destroy', $role) }}"
                                                        onclick="confirmDeleteRole(this)">
                                                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="fas fa-shield-halved fa-2x mb-2 d-block opacity-30" aria-hidden="true"></i>
                                        No roles found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>{{-- /container --}}


    {{-- ── Delete Confirmation Modal ───────────────────────────────────────────── --}}
    <div class="modal fade" id="deleteRoleModal" tabindex="-1" aria-labelledby="deleteRoleModalLabel"
         aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-danger fw-semibold" id="deleteRoleModalLabel">
                        <i class="fas fa-triangle-exclamation me-2" aria-hidden="true"></i>Delete Role
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Cancel deletion"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="mb-0 small">
                        Delete role <strong id="deleteRoleName"></strong>?
                        Users assigned to this role will be moved to the default <em>user</em> role.
                    </p>
                </div>
                <div class="modal-footer border-0 pt-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteRoleForm" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDeleteRole(btn) {
            document.getElementById('deleteRoleName').textContent = btn.getAttribute('data-role-name');
            document.getElementById('deleteRoleForm').action = btn.getAttribute('data-delete-url');
            new bootstrap.Modal(document.getElementById('deleteRoleModal')).show();
        }

        document.addEventListener('DOMContentLoaded', function () {
            var content = document.querySelector('.page-content');
            if (content) content.style.display = 'block';
            var overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.style.display = 'none';
        });
    </script>
@endsection
