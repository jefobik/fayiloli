@extends('layouts.app')

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@php
    use App\Enums\TenantModule;

    $tenant = tenancy()->tenant ?? null;
    $authUser = Auth::user();

    // ── Module launchpad cards ─────────────────────────────────────────────────
    $moduleCards = collect([
        ['module' => TenantModule::DOCUMENTS, 'permission' => 'view documents', 'icon' => 'fa-file-alt', 'color' => '#4285F4', 'bg' => '#e8f0fe'],
        ['module' => TenantModule::FOLDERS, 'permission' => 'view folders', 'icon' => 'fa-folder', 'color' => '#FBBC04', 'bg' => '#fef7e0'],
        ['module' => TenantModule::TAGS, 'permission' => 'view tags', 'icon' => 'fa-tags', 'color' => '#9c27b0', 'bg' => '#f3e5f5'],
        ['module' => TenantModule::USERS, 'permission' => 'view users', 'icon' => 'fa-users', 'color' => '#1e8e3e', 'bg' => '#e6f4ea'],
        ['module' => TenantModule::FILE_REQUESTS, 'permission' => 'view documents', 'icon' => 'fa-file-import', 'color' => '#E37400', 'bg' => '#fef3e2'],
        ['module' => TenantModule::SHARES, 'permission' => 'share documents', 'icon' => 'fa-share-nodes', 'color' => '#1a73e8', 'bg' => '#e8f0fe'],
        ['module' => TenantModule::NOTIFICATIONS, 'permission' => 'view notifications', 'icon' => 'fa-bell', 'color' => '#c5221f', 'bg' => '#fce8e6'],
        ['module' => TenantModule::PROJECTS, 'permission' => null, 'icon' => 'fa-diagram-project', 'color' => '#00897b', 'bg' => '#e0f2f1'],
        ['module' => TenantModule::HRM, 'permission' => null, 'icon' => 'fa-id-badge', 'color' => '#3949ab', 'bg' => '#e8eaf6'],
        ['module' => TenantModule::STATS, 'permission' => null, 'icon' => 'fa-chart-bar', 'color' => '#6d28d9', 'bg' => '#f5f3ff'],
    ])->filter(function (array $card) use ($tenant, $authUser): bool {
        $enabled = $tenant?->hasModule($card['module']) ?? false;
        $allowed = $card['permission'] ? ($authUser?->can($card['permission']) ?? false) : true;
        return $enabled && $allowed;
    });

    $userRoles = $authUser?->getRoleNames()->toArray() ?? [];
    $isAdmin = in_array('admin', $userRoles);
    $isManager = in_array('manager', $userRoles);
    $canUpload = $authUser?->can('create documents') ?? false;
    $canDocs = ($tenant?->hasModule(TenantModule::DOCUMENTS) ?? false) && ($authUser?->can('view documents') ?? false);
    $canUsers = ($tenant?->hasModule(TenantModule::USERS) ?? false) && ($authUser?->can('view users') ?? false);
@endphp

@section('content')
    <div class="space-y-5">

        {{-- ══════════════════════════════════════════════════════════════════
             HERO BAND — GW Philosophy: white panel, flat, functional.
             The greeting IS the hero. No orbs, no gradients, no decoration.
             Google Drive home is a white surface. That's the standard.
        ══════════════════════════════════════════════════════════════════════ --}}
        <section class="gw-hero-band" aria-label="Dashboard greeting"
                 x-data="{
                     greeting: (function() {
                         const h = new Date().getHours();
                         return h < 12 ? 'Good morning'
                              : h < 17 ? 'Good afternoon'
                              : 'Good evening';
                     })()
                 }">

            {{-- Top row: greeting + primary CTA --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                <div>
                    {{-- h1: 18px, semibold — GW uses semibold not bold for page headers --}}
                    <h1 class="text-[1.125rem] font-semibold text-[var(--text-main)] tracking-tight leading-snug"
                        x-text="greeting + ', {{ addslashes($authUser?->name ?? 'User') }}'">
                        Welcome back, {{ $authUser?->name ?? 'User' }}
                    </h1>

                    {{-- Contextual meta line —  date · role · tenant --}}
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-0 mt-1 text-[0.8rem] text-[var(--text-muted)]">
                        <time datetime="{{ now()->toIso8601String() }}">
                            {{ now()->format('l, F j') }}
                        </time>
                        <span class="text-[var(--gw-border)]" aria-hidden="true">·</span>
                        <span class="font-medium">{{ ucfirst($userRoles[0] ?? 'Member') }}</span>
                        @if ($tenant)
                            <span class="text-[var(--gw-border)]" aria-hidden="true">·</span>
                            <span>{{ $tenant->short_name ?? $tenant->organization_name }}</span>
                        @endif
                    </div>
                </div>

                {{-- "New" button — GW blue pill, compact --}}
                @if ($canUpload)
                    <button type="button"
                            onclick="uploadFiles()"
                            class="self-start sm:self-auto inline-flex items-center gap-2
                                   h-9 px-4 rounded-[var(--radius-pill)]
                                   bg-[var(--gw-blue-600)] hover:bg-[var(--gw-blue-700)]
                                   text-white text-[0.8125rem] font-semibold leading-none
                                   shadow-sm hover:shadow transition-all shrink-0"
                            aria-label="Upload a new document">
                        <i class="fas fa-plus text-[0.6rem]" aria-hidden="true"></i>
                        New
                    </button>
                @endif
            </div>

            {{-- Quick-action strip — GW: low-profile surface-2 pills, no borders --}}
            <div class="flex items-center gap-1.5 flex-wrap mt-4 pt-3
                        border-t border-[var(--gw-border-subtle)]">

                @if ($canDocs)
                    <a href="{{ route('documents.index') }}"
                       wire:navigate
                       class="inline-flex items-center gap-1.5 h-7 px-3
                              rounded-[var(--radius-pill)]
                              bg-[var(--gw-surface-2)] hover:bg-[var(--gw-surface-active)]
                              text-[0.75rem] font-medium text-[var(--text-muted)]
                              hover:text-[var(--gw-blue-600)]
                              transition-colors no-underline">
                        <i class="fas fa-file-alt text-[var(--gw-blue-500)] text-[0.6rem]" aria-hidden="true"></i>
                        Documents
                    </a>
                @endif

                @if ($canUpload)
                    <button type="button" onclick="uploadFiles()"
                            class="inline-flex items-center gap-1.5 h-7 px-3
                                   rounded-[var(--radius-pill)]
                                   bg-[var(--gw-surface-2)] hover:bg-[var(--gw-surface-active)]
                                   text-[0.75rem] font-medium text-[var(--text-muted)]
                                   hover:text-[var(--gw-blue-600)]
                                   transition-colors">
                        <i class="fas fa-arrow-up-from-bracket text-[var(--text-ghost)] text-[0.6rem]" aria-hidden="true"></i>
                        Upload
                    </button>
                @endif

                {{-- Spacer --}}
                <div class="flex-1" aria-hidden="true"></div>

                {{-- ⌘K keyboard hint --}}
                <div class="hidden sm:flex items-center gap-1.5 text-[0.72rem] text-[var(--text-ghost)]"
                     aria-hidden="true">
                    <span>Search</span>
                    <kbd class="inline-flex items-center px-1.5 py-0.5
                                text-[0.6rem] font-semibold tracking-wider
                                border border-[var(--gw-border)] rounded-[4px]
                                bg-[var(--gw-surface-2)] text-[var(--text-ghost)]">⌘K</kbd>
                </div>
            </div>
        </section>

        {{-- ══════════════════════════════════════════════════════════════════
             MAIN GRID — 2-col on xl+: content left, sidebar right
        ══════════════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 xl:grid-cols-[1fr_296px] gap-5">

            {{-- ── LEFT COLUMN ────────────────────────────────────────── --}}
            <div class="space-y-5 min-w-0">

                {{-- KPI Stats component --}}
                <livewire:dashboard-stats />

                {{-- Module Launchpad --}}
                @if($moduleCards->isNotEmpty())
                    <section aria-labelledby="launchpad-heading">
                        <h2 id="launchpad-heading"
                            class="text-[0.6875rem] font-bold tracking-[0.08em] uppercase
                                   text-[var(--text-ghost)] mb-2.5">
                            Apps
                        </h2>
                        <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 lg:grid-cols-5 xl:grid-cols-5 gap-2.5">
                            @foreach($moduleCards as $card)
                                @php
                                    $mod = $card['module'];
                                    try {
                                        $landingUrl = route($mod->landingRoute());
                                    } catch (\Exception) {
                                        $landingUrl = '#';
                                    }
                                @endphp
                                <a href="{{ $landingUrl }}"
                                   wire:navigate
                                   class="gw-launchpad-card group flex flex-col items-center gap-2 p-3
                                          rounded-[var(--radius-md)]
                                          bg-[var(--gw-surface,#fff)]
                                          border border-[var(--gw-border)]
                                          hover:shadow-[var(--elevation-2)]
                                          hover:-translate-y-0.5
                                          transition-all duration-200 no-underline text-center">
                                    {{-- Icon chip — flat circle, Google product-icon style --}}
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center
                                                text-[1rem] shrink-0
                                                group-hover:scale-105 transition-transform duration-200"
                                         style="background: {{ $card['bg'] }}; color: {{ $card['color'] }};"
                                         aria-hidden="true">
                                        <i class="fa-solid {{ $card['icon'] }}"></i>
                                    </div>
                                    <span class="text-[0.7rem] font-medium text-[var(--text-muted)]
                                                 group-hover:text-[var(--gw-blue-600)]
                                                 transition-colors leading-tight">
                                        {{ $mod->label() }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Recent Documents --}}
                <livewire:recent-documents />
            </div>

            {{-- ── RIGHT SIDEBAR ───────────────────────────────────────── --}}
            <aside class="space-y-4 min-w-0" aria-label="Dashboard sidebar">

                {{-- Admin role alert --}}
                @if ($isAdmin)
                    <div class="rounded-[var(--radius-md)]
                                bg-[var(--gw-surface-active,#e8f0fe)]
                                border border-[var(--gw-blue-100)]
                                px-4 py-3 flex items-start gap-3">
                        <i class="fas fa-shield-halved text-[var(--gw-blue-600)] text-sm mt-0.5 shrink-0"
                           aria-hidden="true"></i>
                        <div class="flex-1 min-w-0">
                            <p class="text-[0.8rem] font-semibold text-[var(--gw-blue-700)]">
                                Administrator
                            </p>
                            <p class="text-[0.75rem] text-[var(--text-muted)] mt-0.5 leading-relaxed">
                                You have full access to manage users, roles, and workspace settings.
                            </p>
                            @if ($canUsers)
                                <a href="{{ route('users.index') }}"
                                   wire:navigate
                                   class="inline-flex items-center gap-1 mt-2 text-[0.75rem] font-semibold
                                          text-[var(--gw-blue-600)] hover:underline no-underline">
                                    Manage users
                                    <i class="fas fa-arrow-right text-[0.55rem]" aria-hidden="true"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @elseif ($isManager)
                    <div class="rounded-[var(--radius-md)]
                                bg-[var(--gw-surface-2)]
                                border border-[var(--gw-border)]
                                px-4 py-3 flex items-start gap-3">
                        <i class="fas fa-user-tie text-[var(--text-muted)] text-sm mt-0.5 shrink-0"
                           aria-hidden="true"></i>
                        <p class="text-[0.75rem] text-[var(--text-muted)] leading-relaxed">
                            <span class="font-semibold text-[var(--text-main)]">Manager</span> —
                            you can view users and the audit log.
                        </p>
                    </div>
                @endif

                {{-- Quick Navigation card --}}
                @if($moduleCards->isNotEmpty())
                    <div class="rounded-[var(--radius-md)]
                                bg-[var(--gw-surface,#fff)]
                                border border-[var(--gw-border)]
                                overflow-hidden">
                        <div class="px-4 py-2.5 border-b border-[var(--gw-border)]">
                            <h3 class="text-[0.6875rem] font-bold tracking-[0.08em] uppercase
                                       text-[var(--text-ghost)]">
                                Quick Navigation
                            </h3>
                        </div>
                        <nav aria-label="Quick module navigation" class="py-1">
                            @foreach($moduleCards as $card)
                                @php
                                    $mod = $card['module'];
                                    try {
                                        $landingUrl = route($mod->landingRoute());
                                    } catch (\Exception) {
                                        $landingUrl = '#';
                                    }
                                @endphp
                                <a href="{{ $landingUrl }}"
                                   wire:navigate
                                   class="group flex items-center gap-3 px-4 py-2 no-underline
                                          hover:bg-[var(--gw-surface-hover)] transition-colors">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-[0.6rem] shrink-0"
                                         style="background: {{ $card['bg'] }}; color: {{ $card['color'] }};"
                                         aria-hidden="true">
                                        <i class="fa-solid {{ $card['icon'] }}"></i>
                                    </div>
                                    <span class="flex-1 text-[0.8125rem] font-medium text-[var(--text-main)]
                                                 group-hover:text-[var(--gw-blue-600)]
                                                 transition-colors truncate">
                                        {{ $mod->label() }}
                                    </span>
                                    <i class="fas fa-chevron-right text-[0.5rem] text-[var(--text-ghost)]
                                              group-hover:text-[var(--gw-blue-500)] transition-colors shrink-0"
                                       aria-hidden="true"></i>
                                </a>
                            @endforeach
                        </nav>
                    </div>
                @endif
            </aside>
        </div>
    </div>
@endsection