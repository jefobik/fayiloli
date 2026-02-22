@extends('layouts.app')

@php
    use App\Enums\TenantModule;

    $tenant   = tenancy()->tenant ?? null;
    $authUser = Auth::user();

    // ── Module launchpad: two-layer gate (tenant enabled + Spatie permission) ──
    $moduleCards = collect([
        ['module' => TenantModule::DOCUMENTS,     'permission' => 'view documents',     'color' => '#4f46e5', 'bg' => 'rgba(79,70,229,0.08)',   'border' => 'rgba(79,70,229,0.2)'],
        ['module' => TenantModule::FOLDERS,       'permission' => 'view folders',       'color' => '#0284c7', 'bg' => 'rgba(2,132,199,0.08)',    'border' => 'rgba(2,132,199,0.2)'],
        ['module' => TenantModule::TAGS,          'permission' => 'view tags',          'color' => '#7c3aed', 'bg' => 'rgba(124,58,237,0.08)',   'border' => 'rgba(124,58,237,0.2)'],
        ['module' => TenantModule::USERS,         'permission' => 'view users',         'color' => '#0369a1', 'bg' => 'rgba(3,105,161,0.08)',    'border' => 'rgba(3,105,161,0.2)'],
        ['module' => TenantModule::FILE_REQUESTS, 'permission' => 'view documents',     'color' => '#d97706', 'bg' => 'rgba(217,119,6,0.08)',    'border' => 'rgba(217,119,6,0.2)'],
        ['module' => TenantModule::SHARES,        'permission' => 'share documents',    'color' => '#059669', 'bg' => 'rgba(5,150,105,0.08)',    'border' => 'rgba(5,150,105,0.2)'],
        ['module' => TenantModule::NOTIFICATIONS, 'permission' => 'view notifications', 'color' => '#dc2626', 'bg' => 'rgba(220,38,38,0.08)',    'border' => 'rgba(220,38,38,0.2)'],
        ['module' => TenantModule::PROJECTS,      'permission' => null,                 'color' => '#0891b2', 'bg' => 'rgba(8,145,178,0.08)',    'border' => 'rgba(8,145,178,0.2)'],
        ['module' => TenantModule::CONTACTS,      'permission' => null,                 'color' => '#16a34a', 'bg' => 'rgba(22,163,74,0.08)',    'border' => 'rgba(22,163,74,0.2)'],
    ])->filter(function (array $card) use ($tenant, $authUser): bool {
        $enabled = $tenant?->hasModule($card['module']) ?? false;
        $allowed = $card['permission'] ? ($authUser?->can($card['permission']) ?? false) : true;
        return $enabled && $allowed;
    });

    $userRoles = $authUser?->getRoleNames()->toArray() ?? [];
    $isAdmin   = in_array('admin', $userRoles);
    $isManager = in_array('manager', $userRoles);

    $canUpload = $authUser?->can('create documents') ?? false;
    $canDocs   = ($tenant?->hasModule(TenantModule::DOCUMENTS) ?? false)
                 && ($authUser?->can('view documents') ?? false);
    $canTags   = ($tenant?->hasModule(TenantModule::TAGS) ?? false)
                 && ($authUser?->can('view tags') ?? false);
    $canUsers  = ($tenant?->hasModule(TenantModule::USERS) ?? false)
                 && ($authUser?->can('view users') ?? false);
@endphp

@section('content')

<div
    class="dashboard-wrap"
    x-data="{
        /*
         * Time-of-day greeting — computed once on mount and never re-evaluated,
         * so it does not cause reactive churn on the page.
         */
        greeting: (function() {
            const h = new Date().getHours();
            return h < 12 ? 'Good morning'
                 : h < 17 ? 'Good afternoon'
                 :           'Good evening';
        })(),

        /* Upload dropdown state */
        uploadOpen: false,
        closeUpload() { this.uploadOpen = false; }
    }"
>

    {{-- ── Page header ─────────────────────────────────────────────────── --}}
    <div class="dash-header">
        <div>
            {{--
                Heading uses x-text so Alpine replaces "Welcome back" with the
                time-aware greeting after JS loads.  The hard-coded text is the
                no-JS / pre-hydration fallback — identical appearance, just a
                different salutation.
            --}}
            <h1 class="dash-title"
                x-text="greeting + ', {{ addslashes($authUser?->name ?? 'User') }}'">
                Welcome back, {{ $authUser?->name ?? 'User' }}
            </h1>
            <div class="dash-meta">
                <span>{{ now()->format('l, F j, Y') }}</span>
                <span class="dash-sep">&middot;</span>
                <span class="tenant-badge">
                    <i class="fas fa-shield-alt" style="font-size:0.6rem"></i>
                    {{ ucfirst($userRoles[0] ?? 'Member') }}
                </span>
                @if ($tenant)
                    <span class="dash-sep">&middot;</span>
                    <span style="font-size:0.78rem;color:#94a3b8">
                        <i class="fas fa-building" style="font-size:0.65rem"></i>
                        {{ $tenant->organization_name }}
                    </span>
                @endif
            </div>
        </div>

        {{-- ── Quick actions (role-gated) ──────────────────────────── --}}
        <div class="dash-actions">

            @if ($canDocs)
                <a href="{{ route('documents.index') }}" class="toolbar-btn toolbar-btn-primary">
                    <i class="fas fa-file-alt"></i>
                    <span>Documents</span>
                </a>
            @endif

            {{-- Upload dropdown — Alpine-powered, calls existing JS helpers --}}
            @if ($canUpload)
                <div class="relative" style="position:relative"
                     x-data="{ open: false }" @click.outside="open = false">

                    <button
                        type="button"
                        class="toolbar-btn toolbar-btn-outline"
                        @click="open = !open"
                        :aria-expanded="open.toString()"
                        aria-haspopup="true"
                        aria-controls="uploadMenu"
                    >
                        <i class="fas fa-upload"></i>
                        <span>Upload</span>
                        <i class="fas fa-chevron-down"
                           :class="open ? 'dash-chevron-up' : 'dash-chevron-dn'"
                           aria-hidden="true"></i>
                    </button>

                    <div
                        id="uploadMenu"
                        class="dash-upload-menu"
                        x-show="open"
                        x-cloak
                        x-transition:enter="dash-menu-enter"
                        x-transition:enter-start="dash-menu-enter-from"
                        x-transition:enter-end="dash-menu-enter-to"
                        x-transition:leave="dash-menu-leave"
                        x-transition:leave-start="dash-menu-leave-from"
                        x-transition:leave-end="dash-menu-leave-to"
                        role="menu"
                        aria-label="Upload options"
                    >
                        <button type="button" role="menuitem" class="dash-upload-item"
                                @click="uploadFiles(); open = false">
                            <i class="fas fa-file-lines" aria-hidden="true"></i>
                            Upload Files
                        </button>
                        <button type="button" role="menuitem" class="dash-upload-item"
                                @click="uploadFolder(); open = false">
                            <i class="fas fa-folder-open" aria-hidden="true"></i>
                            Upload Folder
                        </button>
                        <button type="button" role="menuitem" class="dash-upload-item"
                                @click="addUrlModal(); open = false">
                            <i class="fas fa-link" aria-hidden="true"></i>
                            Add URL
                        </button>
                    </div>
                </div>
            @endif

            @if ($canTags)
                <a href="{{ route('tags.index') }}" class="toolbar-btn toolbar-btn-outline">
                    <i class="fas fa-tags"></i>
                    <span>Tags</span>
                </a>
            @endif

        </div>
    </div>

    {{-- ── Live stats (Livewire — polls every 60 s) ────────────────────── --}}
    <livewire:dashboard-stats />

    {{-- ── Module launchpad ────────────────────────────────────────────── --}}
    @if ($moduleCards->isNotEmpty())
        <div class="dash-section-label">
            Your Workspace Modules
            <span class="dash-module-count">{{ $moduleCards->count() }}</span>
        </div>

        <div class="module-grid" role="list" aria-label="Available workspace modules">
            @foreach ($moduleCards as $card)
                @php
                    $mod = $card['module'];
                    try { $landingUrl = route($mod->landingRoute()); } catch (\Exception) { $landingUrl = '#'; }
                @endphp

                <div class="module-card"
                     role="listitem"
                     x-data="{ hover: false }"
                     @mouseenter="hover = true"
                     @mouseleave="hover = false">

                    <div class="module-card-icon"
                         :style="hover
                            ? 'color:{{ $card['color'] }};background:{{ str_replace('0.08', '0.15', $card['bg']) }};border-color:{{ str_replace('0.2', '0.4', $card['border']) }}'
                            : 'color:{{ $card['color'] }};background:{{ $card['bg'] }};border-color:{{ $card['border'] }}'">
                        <i class="fa-solid fa-{{ $mod->icon() }}" aria-hidden="true"></i>
                    </div>

                    <div class="module-card-body">
                        <div class="module-card-title">{{ $mod->label() }}</div>
                        <div class="module-card-desc">{{ $mod->description() }}</div>
                    </div>

                    @if ($landingUrl !== '#')
                        <a href="{{ $landingUrl }}"
                           class="module-card-btn"
                           style="color:{{ $card['color'] }};border-color:{{ $card['border'] }}"
                           aria-label="Open {{ $mod->label() }}">
                            Open <i class="fas fa-arrow-right" aria-hidden="true" style="font-size:0.7rem"></i>
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="module-empty">
            <i class="fas fa-lock" aria-hidden="true"></i>
            <p>No modules are currently available for your account.</p>
            <p style="font-size:0.8rem;color:#94a3b8;margin-top:0.25rem">
                Contact your workspace administrator to request access.
            </p>
        </div>
    @endif

    {{-- ── Role hint bars ───────────────────────────────────────────────── --}}
    @if ($isAdmin)
        <div class="admin-hint-bar" role="note" aria-label="Administrator notice">
            <i class="fas fa-user-shield" aria-hidden="true"></i>
            <span>
                You have <strong>Administrator</strong> access —
                you can manage users, roles, and workspace settings.
            </span>
            @if ($canUsers)
                <a href="{{ route('users.index') }}" class="admin-hint-link">Manage Users</a>
            @endif
        </div>
    @elseif ($isManager)
        <div class="admin-hint-bar" style="--hint-color:#0284c7" role="note" aria-label="Manager notice">
            <i class="fas fa-user-tie" aria-hidden="true"></i>
            <span>You have <strong>Manager</strong> access — you can view users and the audit log.</span>
        </div>
    @endif

</div>{{-- /dashboard-wrap --}}

{{-- Show page content immediately on the home page --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    var content = document.querySelector('.page-content');
    if (content) content.style.display = 'block';
    var overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.style.display = 'none';
});
</script>

<style>
/* Dashboard shell */
.dashboard-wrap { padding: 1.5rem; }

/* Header */
.dash-header {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;
}
.dash-title { font-size: 1.4rem; font-weight: 800; color: #1e293b; margin: 0; }
.dash-meta {
    display: flex; align-items: center; flex-wrap: wrap;
    gap: 0.4rem; color: #64748b; font-size: 0.8rem; margin-top: 0.2rem;
}
.dash-sep { color: #cbd5e1; }
.dash-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; }

/* Chevron rotation for upload dropdown */
.dash-chevron-dn, .dash-chevron-up {
    font-size: 0.62rem;
    transition: transform 0.15s ease;
}
.dash-chevron-up { transform: rotate(180deg); }

/* Upload dropdown menu */
.dash-upload-menu {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    min-width: 180px;
    z-index: 100;
    overflow: hidden;
}
.dash-upload-item {
    display: flex; align-items: center; gap: 0.6rem;
    width: 100%; padding: 0.65rem 1rem;
    background: none; border: none;
    color: #374151; font-size: 0.82rem; font-weight: 500;
    font-family: inherit; cursor: pointer; text-align: left;
    transition: background 0.12s, color 0.12s;
}
.dash-upload-item i { color: #7c3aed; font-size: 0.85rem; width: 1rem; text-align: center; }
.dash-upload-item:hover { background: #f5f3ff; color: #4f46e5; }

/* Alpine transition classes for upload menu */
.dash-menu-enter      { transition: opacity 0.12s ease-out, transform 0.12s ease-out; }
.dash-menu-enter-from { opacity: 0; transform: translateY(-4px) scale(0.97); }
.dash-menu-enter-to   { opacity: 1; transform: translateY(0)    scale(1);    }
.dash-menu-leave      { transition: opacity 0.08s ease-in, transform 0.08s ease-in; }
.dash-menu-leave-from { opacity: 1; transform: translateY(0)    scale(1);    }
.dash-menu-leave-to   { opacity: 0; transform: translateY(-4px) scale(0.97); }

/* Section label */
.dash-section-label {
    display: flex; align-items: center; gap: 0.5rem;
    font-size: 0.72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.08em;
    color: #94a3b8; margin: 2rem 0 0.875rem;
}
.dash-module-count {
    background: rgba(124,58,237,0.1);
    color: #7c3aed;
    font-size: 0.65rem; font-weight: 700;
    padding: 0.1rem 0.42rem; border-radius: 999px;
}

/* Module grid */
.module-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 0.875rem;
}

/* Module card */
.module-card {
    background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px;
    padding: 1.1rem 1.1rem 0.9rem;
    display: flex; flex-direction: column; gap: 0.6rem;
    transition: box-shadow 0.18s, border-color 0.18s, transform 0.15s;
}
.module-card:hover {
    box-shadow: 0 4px 18px rgba(0,0,0,0.08); transform: translateY(-1px);
}
.module-card-icon {
    width: 44px; height: 44px; border-radius: 10px; border: 1px solid;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem; flex-shrink: 0;
    transition: background 0.18s, border-color 0.18s;
}
.module-card-body { flex: 1; }
.module-card-title { font-size: 0.88rem; font-weight: 700; color: #1e293b; margin-bottom: 0.2rem; }
.module-card-desc { font-size: 0.76rem; color: #64748b; line-height: 1.45; }
.module-card-btn {
    display: inline-flex; align-items: center; gap: 0.35rem;
    font-size: 0.75rem; font-weight: 600;
    border: 1px solid; border-radius: 6px;
    padding: 0.35rem 0.75rem;
    align-self: flex-start;
    transition: opacity 0.15s;
}
.module-card-btn:hover { opacity: 0.8; }

/* Empty state */
.module-empty {
    text-align: center; padding: 3.5rem 1rem; color: #64748b; margin-top: 1.5rem;
}
.module-empty i { font-size: 2.25rem; opacity: 0.4; display: block; margin-bottom: 0.75rem; }
.module-empty p { margin: 0; font-size: 0.875rem; }

/* Admin hint bar */
.admin-hint-bar {
    --hint-color: #7c3aed;
    display: flex; align-items: center; gap: 0.6rem;
    margin-top: 1.5rem; padding: 0.75rem 1rem;
    background: rgba(124,58,237,0.05);
    border: 1px solid rgba(124,58,237,0.18);
    border-left: 3px solid var(--hint-color);
    border-radius: 8px; font-size: 0.8rem; color: #475569;
}
.admin-hint-bar i { color: var(--hint-color); font-size: 0.875rem; flex-shrink: 0; }
.admin-hint-link {
    margin-left: auto; font-size: 0.75rem; font-weight: 600;
    color: var(--hint-color); white-space: nowrap;
}
.admin-hint-link:hover { text-decoration: underline; }

/* Dark mode */
body.dark-mode .module-card  { background: #1e293b; border-color: #334155; }
body.dark-mode .module-card-title { color: #e2e8f0; }
body.dark-mode .module-card-desc  { color: #94a3b8; }
body.dark-mode .dash-title  { color: #f1f5f9; }
body.dark-mode .admin-hint-bar { background: rgba(124,58,237,0.1); border-color: rgba(124,58,237,0.3); }
body.dark-mode .dash-upload-menu { background: #1e293b; border-color: #334155; }
body.dark-mode .dash-upload-item { color: #cbd5e1; }
body.dark-mode .dash-upload-item:hover { background: #312e81; color: #a78bfa; }

@media (max-width: 640px) {
    .dashboard-wrap { padding: 1rem; }
    .module-grid { grid-template-columns: 1fr; }
}
</style>

@endsection
