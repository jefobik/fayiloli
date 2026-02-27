@extends('layouts.app')
@section('content')
    <div class="container py-4"
         x-data="{
             density: localStorage.getItem('tableDensity') || 'relaxed',
             search: '{{ request('search', '') }}'
         }"
         x-init="$watch('density', val => localStorage.setItem('tableDensity', val))">

        {{-- ── Page Header ──────────────────────────────────────────────────────── --}}
        <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
            <div>
                <h1 class="h4 fw-bold mb-0" style="color:#1e293b">
                    <i class="fas fa-users me-2 text-indigo-600" aria-hidden="true"></i>User Management
                </h1>
                <p class="text-muted small mb-0 mt-1">Manage workspace users, roles and account status</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button"
                        @click="density = density === 'relaxed' ? 'compact' : 'relaxed'"
                        class="btn btn-outline-secondary btn-sm"
                        aria-label="Toggle table density" title="Toggle density">
                    <i class="fas"
                       :class="density === 'relaxed' ? 'fa-compress-arrows-alt' : 'fa-expand-arrows-alt'"
                       aria-hidden="true"></i>
                </button>
                @can('create', App\Models\User::class)
                    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm shadow-sm">
                        <i class="fas fa-user-plus me-1" aria-hidden="true"></i>Add User
                    </a>
                @endcan
            </div>
        </div>

        {{-- ── Stats Row ────────────────────────────────────────────────────────── --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <a href="{{ route('users.index') }}"
                   class="card border-0 shadow-sm text-decoration-none d-block h-100 transition-all"
                   style="border-radius:10px;{{ !request()->hasAny(['status','role','search']) ? 'border-left:4px solid #4f46e5 !important' : '' }}">
                    <div class="card-body py-3 px-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-muted fw-semibold text-uppercase" style="font-size:0.68rem;letter-spacing:.06em">Total</span>
                            <div class="d-flex align-items-center justify-content-center rounded-circle"
                                 style="width:30px;height:30px;background:rgba(79,70,229,.1)">
                                <i class="fas fa-users" style="font-size:0.8rem;color:#4f46e5" aria-hidden="true"></i>
                            </div>
                        </div>
                        <div class="fw-bold" style="font-size:1.5rem;color:#1e293b;line-height:1">{{ $stats['total'] }}</div>
                        <div class="text-muted mt-1" style="font-size:0.72rem">All workspace users</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="{{ route('users.index', ['status' => 'active']) }}"
                   class="card border-0 shadow-sm text-decoration-none d-block h-100"
                   style="border-radius:10px;{{ request('status') === 'active' ? 'border-left:4px solid #16a34a !important' : '' }}">
                    <div class="card-body py-3 px-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-muted fw-semibold text-uppercase" style="font-size:0.68rem;letter-spacing:.06em">Active</span>
                            <div class="d-flex align-items-center justify-content-center rounded-circle"
                                 style="width:30px;height:30px;background:rgba(22,163,74,.1)">
                                <i class="fas fa-circle-check" style="font-size:0.8rem;color:#16a34a" aria-hidden="true"></i>
                            </div>
                        </div>
                        <div class="fw-bold" style="font-size:1.5rem;color:#16a34a;line-height:1">{{ $stats['active'] }}</div>
                        <div class="text-muted mt-1" style="font-size:0.72rem">Unlocked &amp; active</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="{{ route('users.index', ['status' => 'inactive']) }}"
                   class="card border-0 shadow-sm text-decoration-none d-block h-100"
                   style="border-radius:10px;{{ request('status') === 'inactive' ? 'border-left:4px solid #dc2626 !important' : '' }}">
                    <div class="card-body py-3 px-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-muted fw-semibold text-uppercase" style="font-size:0.68rem;letter-spacing:.06em">Inactive</span>
                            <div class="d-flex align-items-center justify-content-center rounded-circle"
                                 style="width:30px;height:30px;background:rgba(220,38,38,.1)">
                                <i class="fas fa-circle-xmark" style="font-size:0.8rem;color:#dc2626" aria-hidden="true"></i>
                            </div>
                        </div>
                        <div class="fw-bold" style="font-size:1.5rem;color:#dc2626;line-height:1">{{ $stats['inactive'] }}</div>
                        <div class="text-muted mt-1" style="font-size:0.72rem">Deactivated accounts</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="{{ route('users.index', ['status' => 'locked']) }}"
                   class="card border-0 shadow-sm text-decoration-none d-block h-100"
                   style="border-radius:10px;{{ request('status') === 'locked' ? 'border-left:4px solid #d97706 !important' : '' }}">
                    <div class="card-body py-3 px-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-muted fw-semibold text-uppercase" style="font-size:0.68rem;letter-spacing:.06em">Locked</span>
                            <div class="d-flex align-items-center justify-content-center rounded-circle"
                                 style="width:30px;height:30px;background:rgba(217,119,6,.1)">
                                <i class="fas fa-lock" style="font-size:0.8rem;color:#d97706" aria-hidden="true"></i>
                            </div>
                        </div>
                        <div class="fw-bold" style="font-size:1.5rem;color:#d97706;line-height:1">{{ $stats['locked'] }}</div>
                        <div class="text-muted mt-1" style="font-size:0.72rem">Login locked</div>
                    </div>
                </a>
            </div>
        </div>

        {{-- ── Search / Filter Bar ──────────────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm mb-3" style="border-radius:10px">
            <div class="card-body py-2 px-3">
                <form method="GET" action="{{ route('users.index') }}" role="search"
                      class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="position-relative flex-grow-1" style="max-width:340px">
                        <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted" aria-hidden="true">
                            <i class="fas fa-search" style="font-size:0.75rem"></i>
                        </span>
                        <input type="search" name="search"
                               class="form-control form-control-sm ps-5 border-0 bg-slate-50"
                               placeholder="Search name, email or username…"
                               value="{{ request('search') }}"
                               aria-label="Search users"
                               x-model="search"
                               autocomplete="off">
                        <button type="button"
                                x-show="search.length > 0"
                                @click="search = ''; $el.closest('form').submit()"
                                class="position-absolute top-50 end-0 translate-middle-y pe-2 btn btn-sm btn-link text-muted p-0 me-1"
                                aria-label="Clear search" style="display:none">
                            <i class="fas fa-times-circle" style="font-size:0.8rem"></i>
                        </button>
                    </div>
                    <select name="role" class="form-select form-select-sm border-0 bg-slate-50" style="max-width:155px"
                            aria-label="Filter by role" onchange="this.form.submit()">
                        <option value="">All Roles</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}"
                                    {{ request('role') === $role->name ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                    <select name="status" class="form-select form-select-sm border-0 bg-slate-50" style="max-width:135px"
                            aria-label="Filter by status" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="locked"   {{ request('status') === 'locked'   ? 'selected' : '' }}>Locked</option>
                    </select>
                    <button type="submit" class="btn btn-outline-secondary btn-sm" aria-label="Apply filters">
                        <i class="fas fa-filter me-1" aria-hidden="true"></i>
                        <span class="d-none d-sm-inline">Filter</span>
                    </button>
                    @if (request()->hasAny(['search', 'role', 'status']))
                        <a href="{{ route('users.index') }}"
                           class="btn btn-link btn-sm text-muted text-decoration-none"
                           aria-label="Reset all filters">
                            <i class="fas fa-rotate-left me-1" aria-hidden="true"></i>Reset
                        </a>
                    @endif
                </form>
            </div>
        </div>

        @if (request()->hasAny(['search', 'role', 'status']))
            <p class="text-muted small mb-2" aria-live="polite">
                Showing <strong>{{ $users->total() }}</strong> result{{ $users->total() !== 1 ? 's' : '' }}
                @if (request('search')) for <em>"{{ request('search') }}"</em> @endif
            </p>
        @endif

        {{-- ── Users Table ──────────────────────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm" style="border-radius:10px">
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:62vh;overflow-y:auto;border-radius:10px">
                    <table class="table table-hover align-middle mb-0 w-full" aria-label="User list">
                        <thead class="sticky top-0 z-10"
                               style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                            <tr>
                                <th scope="col" class="ps-4 fw-semibold text-muted text-uppercase"
                                    style="font-size:0.68rem;letter-spacing:.07em;padding-top:.75rem;padding-bottom:.75rem">User</th>
                                <th scope="col" class="fw-semibold text-muted text-uppercase d-none d-md-table-cell"
                                    style="font-size:0.68rem;letter-spacing:.07em">Username</th>
                                <th scope="col" class="fw-semibold text-muted text-uppercase d-none d-lg-table-cell"
                                    style="font-size:0.68rem;letter-spacing:.07em">Phone</th>
                                <th scope="col" class="fw-semibold text-muted text-uppercase"
                                    style="font-size:0.68rem;letter-spacing:.07em">Roles</th>
                                <th scope="col" class="fw-semibold text-muted text-uppercase"
                                    style="font-size:0.68rem;letter-spacing:.07em">Status</th>
                                <th scope="col" class="text-end pe-4 fw-semibold text-muted text-uppercase"
                                    style="font-size:0.68rem;letter-spacing:.07em">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            @forelse($users as $user)
                                <tr class="group transition-colors"
                                    style="border-bottom:1px solid #f1f5f9">
                                    {{-- Name + Email (clickable) --}}
                                    <td class="ps-4 transition-all duration-200"
                                        :class="density === 'compact' ? 'py-1' : 'py-2'">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                                 :style="density === 'compact'
                                                     ? 'width:28px;height:28px;font-size:0.62rem'
                                                     : 'width:36px;height:36px;font-size:0.72rem'"
                                                 style="background:linear-gradient(135deg,#4f46e5,#7c3aed)"
                                                 aria-hidden="true">
                                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}{{ strtoupper(substr(explode(' ', $user->name ?? 'U ')[1] ?? '', 0, 1)) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('users.show', $user) }}"
                                                   class="fw-semibold text-decoration-none d-block"
                                                   style="font-size:0.875rem;color:#1e293b">
                                                    {{ $user->name }}
                                                    @if($user->is_admin)
                                                        <i class="fas fa-shield-halved ms-1 text-indigo-500"
                                                           style="font-size:0.62rem" title="Admin" aria-hidden="true"></i>
                                                    @endif
                                                </a>
                                                <div class="text-muted" style="font-size:0.75rem">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    {{-- Username --}}
                                    <td class="d-none d-md-table-cell"
                                        :class="density === 'compact' ? 'py-1' : 'py-2'">
                                        @if($user->user_name)
                                            <code class="text-secondary px-1 rounded"
                                                  style="font-size:0.8rem;background:#f1f5f9">{{ $user->user_name }}</code>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    {{-- Phone --}}
                                    <td class="d-none d-lg-table-cell"
                                        :class="density === 'compact' ? 'py-1' : 'py-2'">
                                        <span style="font-size:0.85rem">{{ $user->phone ?? '—' }}</span>
                                    </td>
                                    {{-- Roles --}}
                                    <td :class="density === 'compact' ? 'py-1' : 'py-2'">
                                        @forelse($user->getRoleNames() as $role)
                                            <x-ts-badge :text="ucfirst($role)" color="violet" class="me-1" />
                                        @empty
                                            <x-ts-badge text="No Role" color="gray" />
                                        @endforelse
                                    </td>
                                    {{-- Status --}}
                                    <td :class="density === 'compact' ? 'py-1' : 'py-2'">
                                        <div class="d-flex flex-wrap gap-1">
                                            @if($user->is_locked)
                                                <x-ts-badge text="Locked" color="yellow" />
                                            @elseif($user->is_active)
                                                <x-ts-badge text="Active" color="green" />
                                            @else
                                                <x-ts-badge text="Inactive" color="red" />
                                            @endif
                                            @if($user->is_2fa_enabled)
                                                <x-ts-badge text="2FA" color="teal" />
                                            @endif
                                        </div>
                                    </td>
                                    {{-- Actions --}}
                                    <td class="text-end pe-4"
                                        :class="density === 'compact' ? 'py-1' : 'py-2'">
                                        <div class="d-flex justify-content-end align-items-center gap-1
                                                    opacity-100 lg:opacity-0 group-hover:opacity-100 focus-within:opacity-100
                                                    transition-opacity duration-200"
                                             role="group" aria-label="Actions for {{ $user->name }}">
                                            <a href="{{ route('users.show', $user) }}"
                                               class="btn btn-sm btn-outline-secondary"
                                               :class="density === 'compact' ? 'py-0' : ''"
                                               title="View profile" aria-label="View {{ $user->name }}">
                                                <i class="fas fa-eye" aria-hidden="true"></i>
                                            </a>
                                            @can('update', $user)
                                                <a href="{{ route('users.edit', $user) }}"
                                                   class="btn btn-sm btn-outline-primary"
                                                   :class="density === 'compact' ? 'py-0' : ''"
                                                   title="Edit" aria-label="Edit {{ $user->name }}">
                                                    <i class="fas fa-pen" aria-hidden="true"></i>
                                                </a>
                                            @endcan
                                            @can('delete', $user)
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger"
                                                        :class="density === 'compact' ? 'py-0' : ''"
                                                        title="Delete" aria-label="Delete {{ $user->name }}"
                                                        data-user-name="{{ $user->name }}"
                                                        data-delete-url="{{ route('users.destroy', $user) }}"
                                                        onclick="confirmDeleteUser(this)">
                                                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        @if (request()->hasAny(['search', 'role', 'status']))
                                            <i class="fas fa-magnifying-glass-minus fa-2x mb-3 d-block opacity-25" aria-hidden="true"></i>
                                            <p class="fw-semibold text-muted mb-1">No users match your filters</p>
                                            <p class="text-muted small mb-3">Try adjusting the search or filter criteria.</p>
                                            <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-rotate-left me-1" aria-hidden="true"></i>Clear filters
                                            </a>
                                        @else
                                            <i class="fas fa-users-slash fa-2x mb-3 d-block opacity-25" aria-hidden="true"></i>
                                            <p class="fw-semibold text-muted mb-1">No users yet</p>
                                            <p class="text-muted small mb-3">Add the first user to this workspace.</p>
                                            @can('create', App\Models\User::class)
                                                <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-user-plus me-1" aria-hidden="true"></i>Add User
                                                </a>
                                            @endcan
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if(isset($users) && method_exists($users, 'hasPages') && $users->hasPages())
                <div class="card-footer bg-transparent border-top px-4 py-3 d-flex flex-wrap align-items-center justify-content-between gap-2"
                     style="border-color:#f1f5f9 !important">
                    <p class="text-muted small mb-0">
                        Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} users
                    </p>
                    <div>{{ $users->links() }}</div>
                </div>
            @endif
        </div>

    </div>{{-- /container --}}


    {{-- ── Delete Confirmation Modal ─────────────────────────────────────────── --}}
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel"
         aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered" style="max-width:380px">
            <div class="modal-content border-0 shadow-lg" style="border-radius:14px">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                             style="width:44px;height:44px;background:rgba(220,38,38,.1)">
                            <i class="fas fa-triangle-exclamation text-danger" style="font-size:1.1rem" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-danger" id="deleteUserModalLabel">Delete User</h5>
                            <p class="text-muted small mb-0">This action cannot be undone.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"
                            aria-label="Cancel deletion"></button>
                </div>
                <div class="modal-body px-4 pt-3 pb-2">
                    <p class="mb-0 small">
                        Delete <strong id="deleteUserName"></strong>?
                        Their account will be deactivated and removed from all roles.
                    </p>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteUserForm" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger px-3">
                            <i class="fas fa-trash-alt me-1" aria-hidden="true"></i>Delete
                        </button>
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
            var overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.style.display = 'none';
        });
    </script>
@endsection
