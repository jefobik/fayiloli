@extends('layouts.app')
@section('content')
    <div class="container py-4"
         x-data="{
             density: localStorage.getItem('tableDensity') || 'relaxed',
             search: '{{ request('search', '') }}',
             get filteredCount() {
                 return document.querySelectorAll('#userTableBody tr[data-searchable]').length;
             }
         }"
         x-init="$watch('density', val => localStorage.setItem('tableDensity', val))">

        {{-- ── Header ──────────────────────────────────────────────────────────── --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h1 class="h4 fw-bold text-primary mb-0">
                    <i class="fas fa-users me-2" aria-hidden="true"></i>User Management
                </h1>
                <p class="text-muted small mb-0">Manage workspace users, roles and account status</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button"
                        @click="density = density === 'relaxed' ? 'compact' : 'relaxed'"
                        class="btn btn-outline-secondary btn-sm"
                        aria-label="Toggle Table Density" title="Toggle Table Density">
                    <i class="fas"
                       :class="density === 'relaxed' ? 'fa-compress-arrows-alt' : 'fa-expand-arrows-alt'"
                       aria-hidden="true"></i>
                </button>
                <a href="{{ route('users.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-user-plus me-1" aria-hidden="true"></i> Add User
                </a>
            </div>
        </div>

        {{-- ── Search / filter bar ─────────────────────────────────────────────── --}}
        <div class="mb-3">
            <form method="GET" action="{{ route('users.index') }}" role="search"
                  class="d-flex align-items-center gap-2 flex-wrap">

                {{-- Search input --}}
                <div class="position-relative flex-grow-1" style="max-width:360px">
                    <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted" aria-hidden="true">
                        <i class="fas fa-search" style="font-size:0.75rem"></i>
                    </span>
                    <input type="search" name="search"
                           class="form-control form-control-sm ps-5"
                           placeholder="Search by name, email, or username…"
                           value="{{ request('search') }}"
                           aria-label="Search users"
                           x-model="search"
                           autocomplete="off">
                    {{-- Clear button --}}
                    <button type="button"
                            x-show="search.length > 0"
                            @click="search = ''; $el.closest('form').submit()"
                            class="position-absolute top-50 end-0 translate-middle-y pe-2 btn btn-sm btn-link text-muted p-0 me-1"
                            aria-label="Clear search" style="display:none">
                        <i class="fas fa-times-circle" style="font-size:0.8rem"></i>
                    </button>
                </div>

                {{-- Role filter --}}
                <select name="role" class="form-select form-select-sm" style="max-width:160px"
                        aria-label="Filter by role" onchange="this.form.submit()">
                    <option value="">All Roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}"
                                {{ request('role') === $role->name ? 'selected' : '' }}>
                            {{ ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>

                {{-- Status filter --}}
                <select name="status" class="form-select form-select-sm" style="max-width:140px"
                        aria-label="Filter by status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="locked"   {{ request('status') === 'locked'   ? 'selected' : '' }}>Locked</option>
                </select>

                {{-- Search submit (hidden — form auto-submits on select change; show on enter or button) --}}
                <button type="submit" class="btn btn-outline-secondary btn-sm" aria-label="Apply filters">
                    <i class="fas fa-filter me-1" aria-hidden="true"></i>
                    <span class="d-none d-sm-inline">Filter</span>
                </button>

                {{-- Reset --}}
                @if (request()->hasAny(['search', 'role', 'status']))
                    <a href="{{ route('users.index') }}"
                       class="btn btn-outline-secondary btn-sm"
                       aria-label="Reset all filters">
                        <i class="fas fa-rotate-left me-1" aria-hidden="true"></i>
                        <span class="d-none d-sm-inline">Reset</span>
                    </a>
                @endif

            </form>
        </div>

        {{-- Result summary --}}
        @if (request()->hasAny(['search', 'role', 'status']))
            <p class="text-muted small mb-2" aria-live="polite">
                Showing <strong>{{ $users->total() }}</strong> result{{ $users->total() !== 1 ? 's' : '' }}
                @if (request('search'))
                    for <em>"{{ request('search') }}"</em>
                @endif
            </p>
        @endif

        {{-- ── Table ───────────────────────────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive overflow-y-auto max-h-[65vh] rounded-b-lg
                            scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                    <table class="table table-hover align-middle mb-0 w-full" aria-label="User list">
                        <thead class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-800 shadow-sm border-b
                                     border-slate-200 dark:border-slate-700 transition-all duration-200">
                            <tr>
                                <th scope="col"
                                    class="ps-4 text-xs font-semibold text-slate-500 uppercase tracking-wider transition-all duration-200"
                                    :class="density === 'compact' ? 'py-2' : 'py-3'">User</th>
                                <th scope="col"
                                    class="text-xs font-semibold text-slate-500 uppercase tracking-wider transition-all duration-200 d-none d-md-table-cell"
                                    :class="density === 'compact' ? 'py-2' : 'py-3'">Username</th>
                                <th scope="col"
                                    class="text-xs font-semibold text-slate-500 uppercase tracking-wider transition-all duration-200 d-none d-lg-table-cell"
                                    :class="density === 'compact' ? 'py-2' : 'py-3'">Phone</th>
                                <th scope="col"
                                    class="text-xs font-semibold text-slate-500 uppercase tracking-wider transition-all duration-200"
                                    :class="density === 'compact' ? 'py-2' : 'py-3'">Roles</th>
                                <th scope="col"
                                    class="text-xs font-semibold text-slate-500 uppercase tracking-wider transition-all duration-200"
                                    :class="density === 'compact' ? 'py-2' : 'py-3'">Status</th>
                                <th scope="col"
                                    class="text-end pe-4 text-xs font-semibold text-slate-500 uppercase tracking-wider transition-all duration-200"
                                    :class="density === 'compact' ? 'py-2' : 'py-3'">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            @forelse($users as $user)
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/50
                                           transition-colors border-b border-slate-100 dark:border-slate-800 last:border-0"
                                    data-searchable>

                                    {{-- Name + email --}}
                                    <td class="ps-4 transition-all duration-200"
                                        :class="density === 'compact' ? 'py-1' : 'py-2'" data-label="User">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar transition-all duration-200 flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                                 :style="density === 'compact'
                                                     ? 'width:28px;height:28px;font-size:0.65rem'
                                                     : 'width:34px;height:34px;font-size:0.72rem'"
                                                 style="background:linear-gradient(135deg,#4f46e5,#7c3aed)"
                                                 aria-hidden="true">
                                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}{{ strtoupper(substr(explode(' ', $user->name ?? 'U ')[1] ?? '', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark" style="font-size:0.875rem">
                                                    {{ $user->name }}
                                                </div>
                                                <div class="text-muted" style="font-size:0.75rem">
                                                    {{ $user->email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Username --}}
                                    <td class="transition-all duration-200 d-none d-md-table-cell"
                                        :class="density === 'compact' ? 'py-1' : 'py-2'" data-label="Username">
                                        @if($user->user_name)
                                            <code class="text-secondary" style="font-size:0.8rem">{{ $user->user_name }}</code>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>

                                    {{-- Phone --}}
                                    <td class="transition-all duration-200 d-none d-lg-table-cell"
                                        :class="density === 'compact' ? 'py-1' : 'py-2'" data-label="Phone">
                                        <span style="font-size:0.85rem">{{ $user->phone ?? '—' }}</span>
                                    </td>

                                    {{-- Roles --}}
                                    <td class="transition-all duration-200"
                                        :class="density === 'compact' ? 'py-1' : 'py-2'" data-label="Roles">
                                        @forelse($user->getRoleNames() as $role)
                                            <x-ts-badge :text="ucfirst($role)" color="violet" class="me-1" />
                                        @empty
                                            <x-ts-badge text="No Role" color="gray" />
                                        @endforelse
                                    </td>

                                    {{-- Status flags --}}
                                    <td class="transition-all duration-200"
                                        :class="density === 'compact' ? 'py-1' : 'py-2'" data-label="Status">
                                        <div class="d-flex flex-wrap gap-1">
                                            @if($user->is_active)
                                                <x-ts-badge text="Active" color="green" />
                                            @else
                                                <x-ts-badge text="Inactive" color="red" />
                                            @endif
                                            @if($user->is_locked)
                                                <x-ts-badge text="Locked" color="yellow" />
                                            @endif
                                            @if($user->is_admin)
                                                <x-ts-badge text="Admin" color="indigo" />
                                            @endif
                                            @if($user->is_2fa_enabled)
                                                <x-ts-badge text="2FA" color="teal" />
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="text-end pe-4 transition-all duration-200"
                                        :class="density === 'compact' ? 'py-1' : 'py-2'" data-label="Actions">
                                        <div class="d-flex justify-content-end gap-1
                                                    opacity-100 lg:opacity-0
                                                    group-hover:opacity-100 focus-within:opacity-100
                                                    transition-opacity duration-200"
                                             role="group" aria-label="Actions for {{ $user->name }}">
                                            <a href="{{ route('users.edit', $user) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               :class="density === 'compact' ? 'btn-sm py-0' : 'btn-sm'"
                                               aria-label="Edit {{ $user->name }}">
                                                <i class="fas fa-pen" aria-hidden="true"
                                                   :class="density === 'compact' ? 'small' : ''"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    :class="density === 'compact' ? 'btn-sm py-0' : 'btn-sm'"
                                                    aria-label="Delete {{ $user->name }}"
                                                    data-user-name="{{ $user->name }}"
                                                    data-delete-url="{{ route('users.destroy', $user) }}"
                                                    onclick="confirmDeleteUser(this)">
                                                <i class="fas fa-trash-alt" aria-hidden="true"
                                                   :class="density === 'compact' ? 'small' : ''"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        @if (request()->hasAny(['search', 'role', 'status']))
                                            <i class="fas fa-search fa-2x mb-2 d-block opacity-30" aria-hidden="true"></i>
                                            No users match your filters.
                                            <a href="{{ route('users.index') }}">Clear filters</a>
                                        @else
                                            <i class="fas fa-users-slash fa-2x mb-2 d-block opacity-30" aria-hidden="true"></i>
                                            No users found.
                                            <a href="{{ route('users.create') }}">Add the first user.</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if(isset($users) && method_exists($users, 'hasPages') && $users->hasPages())
                <div class="card-footer bg-transparent border-0">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

    </div>{{-- /container --}}


    {{-- ── Delete Confirmation Modal ───────────────────────────────────────────── --}}
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel"
         aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-danger fw-semibold" id="deleteUserModalLabel">
                        <i class="fas fa-triangle-exclamation me-2" aria-hidden="true"></i>Delete User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Cancel deletion"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="mb-0 small">
                        Delete <strong id="deleteUserName"></strong>?
                        This action cannot be undone.
                    </p>
                </div>
                <div class="modal-footer border-0 pt-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteUserForm" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDeleteUser(btn) {
            document.getElementById('deleteUserName').textContent = btn.getAttribute('data-user-name');
            document.getElementById('deleteUserForm').action = btn.getAttribute('data-delete-url');
            new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
        }

        document.addEventListener('DOMContentLoaded', function () {
            var content = document.querySelector('.page-content');
            if (content) content.style.display = 'block';
            var overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.style.display = 'none';
        });
    </script>
@endsection
