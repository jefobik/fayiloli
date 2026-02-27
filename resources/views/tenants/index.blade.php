@extends('layouts.central')

@section('title', 'Tenant Management')

@section('content')
    <div class="container-fluid py-4"
         x-data="{ density: localStorage.getItem('tenantTableDensity') || 'relaxed' }"
         x-init="$watch('density', val => localStorage.setItem('tenantTableDensity', val))">

        {{-- ── Header ────────────────────────────────────────────────────────── --}}
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <h1 class="h3 mb-0 fw-semibold">
                    <i class="fa-solid fa-building-user me-2 text-primary" aria-hidden="true"></i>
                    Tenant Management
                </h1>
                <p class="text-muted small mb-0">Manage organisations and their provisioned databases</p>
            </div>
            @can('create', App\Models\Tenant::class)
                <div class="d-flex align-items-center gap-2">
                    <button type="button" @click="density = density === 'relaxed' ? 'compact' : 'relaxed'"
                        class="btn btn-outline-secondary btn-sm" aria-label="Toggle Table Density" title="Toggle Table Density">
                        <i class="fa-solid" :class="density === 'relaxed' ? 'fa-compress' : 'fa-expand'" aria-hidden="true"></i>
                    </button>
                    <a href="{{ route('tenants.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i> New Tenant
                    </a>
                </div>
            @endcan
        </div>

        {{-- ── Stats Row (Tailwind cards with color-border accents) ───────────── --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-5">
            {{-- Total --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border-l-4 border-l-slate-400 border border-slate-100 dark:border-slate-700 shadow-sm px-4 py-3 flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-slate-100 dark:bg-slate-700 shrink-0">
                    <i class="fas fa-building text-slate-500 dark:text-slate-300 text-base" aria-hidden="true"></i>
                </div>
                <div>
                    <div class="text-2xl font-extrabold text-slate-900 dark:text-white leading-none">{{ $stats['total'] }}</div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">Total</div>
                </div>
            </div>
            {{-- Active --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border-l-4 border-l-emerald-500 border border-slate-100 dark:border-slate-700 shadow-sm px-4 py-3 flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 shrink-0">
                    <i class="fas fa-circle-check text-emerald-500 dark:text-emerald-400 text-base" aria-hidden="true"></i>
                </div>
                <div>
                    <div class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-400 leading-none">{{ $stats['active'] }}</div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">Active</div>
                </div>
            </div>
            {{-- Pending --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border-l-4 border-l-amber-400 border border-slate-100 dark:border-slate-700 shadow-sm px-4 py-3 flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-900/30 shrink-0">
                    <i class="fas fa-clock text-amber-500 dark:text-amber-400 text-base" aria-hidden="true"></i>
                </div>
                <div>
                    <div class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 leading-none">{{ $stats['pending'] }}</div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">Pending</div>
                </div>
            </div>
            {{-- Suspended --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border-l-4 border-l-rose-500 border border-slate-100 dark:border-slate-700 shadow-sm px-4 py-3 flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-rose-50 dark:bg-rose-900/30 shrink-0">
                    <i class="fas fa-ban text-rose-500 dark:text-rose-400 text-base" aria-hidden="true"></i>
                </div>
                <div>
                    <div class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 leading-none">{{ $stats['suspended'] }}</div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">Suspended</div>
                </div>
            </div>
            {{-- Inactive --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border-l-4 border-l-slate-300 dark:border-l-slate-600 border border-slate-100 dark:border-slate-700 shadow-sm px-4 py-3 flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-slate-50 dark:bg-slate-700 shrink-0">
                    <i class="fas fa-circle-xmark text-slate-400 dark:text-slate-300 text-base" aria-hidden="true"></i>
                </div>
                <div>
                    <div class="text-2xl font-extrabold text-slate-500 dark:text-slate-300 leading-none">{{ $stats['inactive'] }}</div>
                    <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">Inactive</div>
                </div>
            </div>
        </div>

        {{-- ── Search / Filter Bar ─────────────────────────────────────────────── --}}
        <div class="mb-3">
            <form method="GET" action="{{ route('tenants.index') }}" role="search"
                  class="d-flex align-items-center gap-2 flex-wrap">

                {{-- Search input --}}
                <div class="position-relative" style="max-width:340px;flex:1">
                    <span class="position-absolute top-50 inset-s-0 translate-middle-y ps-3 text-muted" aria-hidden="true">
                        <i class="fas fa-search" style="font-size:0.75rem"></i>
                    </span>
                    <input type="search" name="search"
                           class="form-control form-control-sm ps-5"
                           placeholder="Search org name or admin email…"
                           value="{{ request('search') }}"
                           aria-label="Search tenants"
                           autocomplete="off">
                </div>

                {{-- Type filter --}}
                <select name="type" class="form-select form-select-sm" style="max-width:160px"
                        aria-label="Filter by type" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    @foreach(['government' => 'Government', 'secretariat' => 'Secretariat', 'agency' => 'Agency', 'department' => 'Department', 'unit' => 'Unit'] as $val => $label)
                        <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>

                {{-- Status filter --}}
                <select name="status" class="form-select form-select-sm" style="max-width:140px"
                        aria-label="Filter by status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Active</option>
                    <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="inactive"  {{ request('status') === 'inactive'  ? 'selected' : '' }}>Inactive</option>
                </select>

                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-filter me-1" aria-hidden="true"></i> Filter
                </button>

                @if(request()->hasAny(['search', 'type', 'status']))
                    <a href="{{ route('tenants.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times me-1" aria-hidden="true"></i> Reset
                    </a>
                    <span class="text-muted small ms-1">
                        Showing {{ $tenants->total() }} result{{ $tenants->total() !== 1 ? 's' : '' }}
                    </span>
                @endif
            </form>
        </div>

        {{-- ── Table ──────────────────────────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive overflow-y-auto rounded-b-lg scrollbar-thin scrollbar-thumb-slate-300"
                     style="max-height:65vh">
                    <table class="table table-hover align-middle mb-0 w-full" aria-label="Tenant list">
                        <thead class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-800/90 shadow-sm border-b border-slate-200 dark:border-slate-700 transition-all duration-200">
                            <tr>
                                <th scope="col"
                                    class="ps-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                    :class="density === 'compact' ? 'py-2' : 'py-3'">Organisation</th>
                                <th scope="col"
                                    class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider d-none d-md-table-cell"
                                    :class="density === 'compact' ? 'py-2' : 'py-3'">Admin Email</th>
                                <th scope="col"
                                    class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                    :class="density === 'compact' ? 'py-2' : 'py-3'">Type</th>
                                <th scope="col"
                                    class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider d-none d-lg-table-cell"
                                    :class="density === 'compact' ? 'py-2' : 'py-3'">Domains</th>
                                <th scope="col"
                                    class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                    :class="density === 'compact' ? 'py-2' : 'py-3'">Status</th>
                                <th scope="col"
                                    class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider d-none d-md-table-cell"
                                    :class="density === 'compact' ? 'py-2' : 'py-3'">Created</th>
                                <th scope="col"
                                    class="text-end pe-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"
                                    :class="density === 'compact' ? 'py-2' : 'py-3'">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tenants as $tenant)
                                @php
                                    $isPending = $tenant->status === \App\Enums\TenantStatus::PENDING;
                                    $typeColors = [
                                        'government'  => ['from-red-600', 'to-red-800'],
                                        'secretariat' => ['from-indigo-600', 'to-violet-700'],
                                        'agency'      => ['from-sky-600', 'to-blue-700'],
                                        'department'  => ['from-emerald-600', 'to-green-700'],
                                        'unit'        => ['from-amber-500', 'to-orange-600'],
                                    ];
                                    $tc = $typeColors[$tenant->tenant_type?->value ?? ''] ?? ['from-slate-500', 'to-slate-700'];
                                    $words = array_values(array_filter(explode(' ', $tenant->organization_name)));
                                    $initials = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice($words, 0, 2))));
                                @endphp
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors border-b border-slate-100 dark:border-slate-700/60 last:border-0
                                           {{ $isPending ? 'bg-amber-50/60 dark:bg-amber-900/10' : '' }}">

                                    {{-- Organisation + Avatar --}}
                                    <td class="ps-4 transition-all duration-200"
                                        :class="density === 'compact' ? 'py-1' : 'py-2.5'" data-label="Organisation">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-linear-to-br {{ $tc[0] }} {{ $tc[1] }} text-white text-[0.65rem] font-bold shrink-0 shadow-sm"
                                                 aria-hidden="true">
                                                {{ $initials }}
                                            </div>
                                            <div class="min-w-0">
                                                <a href="{{ route('tenants.show', $tenant) }}"
                                                   class="font-semibold text-slate-900 dark:text-slate-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors no-underline text-sm truncate block">
                                                    {{ $tenant->organization_name ?? '—' }}
                                                </a>
                                                <div class="text-slate-500 dark:text-slate-400 text-xs font-mono truncate">{{ $tenant->id }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Admin Email --}}
                                    <td class="small text-slate-600 dark:text-slate-300 d-none d-md-table-cell transition-all duration-200"
                                        :class="density === 'compact' ? 'py-1' : 'py-2.5'" data-label="Admin Email">
                                        {{ $tenant->admin_email ?? '—' }}
                                    </td>

                                    {{-- Type --}}
                                    <td class="transition-all duration-200" :class="density === 'compact' ? 'py-1' : 'py-2.5'"
                                        data-label="Type">
                                        <span class="badge rounded-pill {{ $tenant->plan_badge }}">
                                            {{ $tenant->plan_label }}
                                        </span>
                                    </td>

                                    {{-- Domains --}}
                                    <td class="d-none d-lg-table-cell transition-all duration-200"
                                        :class="density === 'compact' ? 'py-1' : 'py-2.5'" data-label="Domains">
                                        @forelse ($tenant->domains as $domain)
                                            <span class="badge bg-light text-secondary dark:bg-slate-700 dark:text-slate-300 border dark:border-slate-600 me-1 small font-monospace">{{ $domain->domain }}</span>
                                        @empty
                                            <span class="text-muted dark:text-slate-500 small">None</span>
                                        @endforelse
                                    </td>

                                    {{-- Status --}}
                                    <td class="transition-all duration-200" :class="density === 'compact' ? 'py-1' : 'py-2.5'"
                                        data-label="Status">
                                        @if ($isPending)
                                            <span class="badge {{ $tenant->status_badge }}"
                                                title="Provisioning in progress — activate or reject when ready">
                                                <span class="spinner-grow spinner-grow-sm me-1"
                                                    style="width:.5rem;height:.5rem;vertical-align:.1em" role="status"
                                                    aria-hidden="true"></span>
                                                {{ $tenant->status_label }}
                                            </span>
                                        @else
                                            <span class="badge {{ $tenant->status_badge }}">
                                                <i class="fa-solid fa-{{ $tenant->status_icon }} me-1" aria-hidden="true"></i>
                                                {{ $tenant->status_label }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Created --}}
                                    <td class="text-muted small text-slate-500 dark:text-slate-400 d-none d-md-table-cell transition-all duration-200"
                                        :class="density === 'compact' ? 'py-1' : 'py-2.5'" data-label="Created">
                                        {{ $tenant->created_at->format('d M Y') }}
                                    </td>

                                    {{-- Actions --}}
                                    <td class="text-end pe-4 transition-all duration-200"
                                        :class="density === 'compact' ? 'py-1' : 'py-2.5'" data-label="Actions">
                                        <div class="d-flex justify-content-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 focus-within:opacity-100 transition-opacity duration-200">
                                            {{-- View --}}
                                            <a href="{{ route('tenants.show', $tenant) }}" class="btn btn-outline-secondary"
                                                :class="density === 'compact' ? 'btn-sm py-0' : 'btn-sm'"
                                                title="View {{ $tenant->organization_name }}">
                                                <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                            </a>

                                            {{-- Edit --}}
                                            @if ($isPending)
                                                <button type="button" class="btn btn-outline-primary"
                                                    :class="density === 'compact' ? 'btn-sm py-0' : 'btn-sm'" disabled
                                                    title="Provisioning in progress — edit once the tenant is activated"
                                                    aria-disabled="true">
                                                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                                </button>
                                            @else
                                                <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-outline-primary"
                                                    :class="density === 'compact' ? 'btn-sm py-0' : 'btn-sm'"
                                                    title="Edit {{ $tenant->organization_name }}">
                                                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                                </a>
                                            @endif

                                            {{-- Status transition --}}
                                            @if ($tenant->status && count($tenant->status->allowedTransitions()))
                                                @php $first = $tenant->status->allowedTransitions()[0]; @endphp
                                                <button type="button" class="btn {{ $first['btnClass'] }}"
                                                    :class="density === 'compact' ? 'btn-sm py-0' : 'btn-sm'"
                                                    title="{{ $first['action'] }} {{ $tenant->organization_name }}"
                                                    data-target-status="{{ $first['target']->value }}"
                                                    data-action-label="{{ $first['action'] }}"
                                                    data-tenant-name="{{ $tenant->organization_name }}"
                                                    data-transition-url="{{ route('tenants.transition_status', $tenant) }}"
                                                    onclick="openTransitionModal(this)">
                                                    <i class="fa-solid fa-arrow-right-arrow-left" aria-hidden="true"></i>
                                                </button>
                                            @endif

                                            {{-- Delete (super-admin only) --}}
                                            @can('delete', $tenant)
                                                @if ($isPending)
                                                    <button type="button" class="btn btn-outline-danger"
                                                        :class="density === 'compact' ? 'btn-sm py-0' : 'btn-sm'" disabled
                                                        title="Cannot delete while provisioning is in progress"
                                                        aria-disabled="true">
                                                        <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-outline-danger"
                                                        :class="density === 'compact' ? 'btn-sm py-0' : 'btn-sm'"
                                                        title="Delete {{ $tenant->organization_name }}"
                                                        data-tenant-name="{{ $tenant->organization_name }}"
                                                        data-delete-url="{{ route('tenants.destroy', $tenant) }}"
                                                        onclick="confirmDeleteTenant(this)">
                                                        <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                                    </button>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="flex flex-col items-center gap-3 text-slate-400 dark:text-slate-500 py-4">
                                            <i class="fa-solid fa-building-circle-exclamation fa-2x" aria-hidden="true"></i>
                                            <div>
                                                <div class="font-semibold text-slate-600 dark:text-slate-300 mb-1">No tenants found</div>
                                                @if(request()->hasAny(['search', 'type', 'status']))
                                                    <a href="{{ route('tenants.index') }}" class="text-sm text-indigo-600 hover:underline">Clear filters</a>
                                                @else
                                                    @can('create', App\Models\Tenant::class)
                                                        <a href="{{ route('tenants.create') }}" class="text-sm text-indigo-600 hover:underline">Create the first one.</a>
                                                    @endcan
                                                @endif
                                            </div>
                                        </div>
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

    {{-- ── Status Transition Modal ───────────────────────────────────────────── --}}
    <div class="modal fade" id="transitionModal" tabindex="-1" aria-labelledby="transitionModalLabel" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold" id="transitionModalLabel">
                        <i class="fa-solid fa-arrow-right-arrow-left me-2 text-info" aria-hidden="true"></i>
                        <span id="transitionModalAction"></span> Tenant
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cancel"></button>
                </div>
                <form id="transitionForm" method="POST">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" id="transitionTargetStatus">
                    <div class="modal-body pt-2">
                        <p class="mb-3 small">
                            Confirm: <strong id="transitionActionVerb"></strong>
                            <strong id="transitionTenantName"></strong>.
                        </p>
                        <div>
                            <label for="transitionReason" class="form-label small fw-semibold">
                                Reason <span class="text-muted fw-normal">(optional)</span>
                            </label>
                            <textarea name="reason" id="transitionReason" rows="2" class="form-control form-control-sm"
                                placeholder="Brief explanation for audit trail…"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm" id="transitionSubmitBtn">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Delete Confirmation Modal (super-admin only) ─────────────────────── --}}
    <div class="modal fade" id="deleteTenantModal" tabindex="-1" aria-labelledby="deleteTenantModalLabel" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-danger fw-semibold" id="deleteTenantModalLabel">
                        <i class="fa-solid fa-triangle-exclamation me-2" aria-hidden="true"></i>
                        Permanently Delete Tenant
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cancel deletion"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="mb-1">You are about to permanently delete:</p>
                    <p class="fw-semibold fs-6" id="deleteTenantName"></p>
                    <p class="text-danger small mb-2">
                        This will <strong>drop the tenant's PostgreSQL database</strong>, destroy all
                        EDMS data, and remove all domain registrations.
                        <strong>This action is irrecoverable.</strong>
                    </p>
                    <p class="small mb-1 fw-semibold">Type the organisation name to confirm:</p>
                    <input type="text" id="deleteConfirmInput" class="form-control form-control-sm"
                        placeholder="Organisation name…" autocomplete="off">
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteTenantForm" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" id="deleteConfirmBtn" class="btn btn-danger" disabled>
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
            document.getElementById('deleteTenantName').textContent = name;
            document.getElementById('deleteTenantForm').action = btn.getAttribute('data-delete-url');
            var input  = document.getElementById('deleteConfirmInput');
            var submit = document.getElementById('deleteConfirmBtn');
            input.value  = '';
            submit.disabled = true;
            input.oninput = function () { submit.disabled = input.value.trim() !== name; };
            new bootstrap.Modal(document.getElementById('deleteTenantModal')).show();
            document.getElementById('deleteTenantModal').addEventListener('shown.bs.modal', function handler() {
                input.focus();
                this.removeEventListener('shown.bs.modal', handler);
            });
        }
        function openTransitionModal(btn) {
            var status = btn.getAttribute('data-target-status');
            var action = btn.getAttribute('data-action-label');
            var name   = btn.getAttribute('data-tenant-name');
            var url    = btn.getAttribute('data-transition-url');
            var submit = document.getElementById('transitionSubmitBtn');
            var match  = btn.className.match(/btn-(success|warning|danger|secondary)/);
            submit.className = 'btn btn-sm btn-' + (match ? match[1] : 'primary');
            submit.textContent = action;
            document.getElementById('transitionModalAction').textContent = action;
            document.getElementById('transitionActionVerb').textContent  = action.toLowerCase();
            document.getElementById('transitionTenantName').textContent  = name;
            document.getElementById('transitionTargetStatus').value      = status;
            document.getElementById('transitionForm').action             = url;
            document.getElementById('transitionReason').value           = '';
            new bootstrap.Modal(document.getElementById('transitionModal')).show();
        }
    </script>
@endpush
