{{--
╔═══════════════════════════════════════════════════════════════════════╗
║ ENTERPRISE SIDEBAR v4.0 — Google Workspace Edition                  ║
║                                                                       ║
║ Architecture:                                                         ║
║  • 72px collapsed  ↔  240px expanded (CSS token: --gw-rail-width-*)  ║
║  • Vertical 3px bar active indicator — not a filled chip             ║
║  • 5 sections: Workspace · Documents · Administration · Settings     ║
║  • localStorage persists collapse state across sessions              ║
║  • Arrow-key navigation (↑↓) across all nav items                    ║
║  • Tooltip on every item when collapsed (via x-tooltip)              ║
║  • Role-based + module-based visibility                               ║
║  • Fully Livewire-reactive (toggleSidebar() on AppShell component)   ║
╚═══════════════════════════════════════════════════════════════════════╝
--}}

@php
    use App\Enums\TenantModule;

    $tenant   = tenancy()->tenant ?? null;
    $authUser = Auth::user();

    // ── Module & Permission guards ───────────────────────────────────────────
    $canDocs   = $tenant?->hasModule(TenantModule::DOCUMENTS)     && $authUser?->can('view documents');
    $canTags   = $tenant?->hasModule(TenantModule::TAGS)          && $authUser?->can('view tags');
    $canUsers  = $tenant?->hasModule(TenantModule::USERS)         && $authUser?->can('view users');
    $canRoles  = $tenant?->hasModule(TenantModule::USERS)         && $authUser?->can('manage roles');
    $canProj   = $tenant?->hasModule(TenantModule::PROJECTS)      && $authUser?->can('view projects');
    $canHrm    = $tenant?->hasModule(TenantModule::HRM)           && $authUser?->can('view employees');
    $canStats  = $tenant?->hasModule(TenantModule::STATS)         && $authUser?->can('view stats');
    $canNotif  = $tenant?->hasModule(TenantModule::NOTIFICATIONS) && $authUser?->can('view notifications');
    $canShares = $tenant?->hasModule(TenantModule::SHARES)        && $authUser?->can('share documents');
    $showAdmin = $canUsers || $canRoles || $canProj || $canHrm || $canStats;

    // ── Tenant meta ──────────────────────────────────────────────────────────
    $tenantName   = $tenant?->short_name ?? $tenant?->organization_name ?? 'Workspace';
    $tenantDomain = $tenant?->domains?->first()?->domain ?? null;
    $tenantInitial = strtoupper(substr($tenantName, 0, 1));
@endphp

{{--
    The sidebar Alpine instance is intentionally minimal — collapse state
    is owned by the parent AppShell's $wire.sidebarCollapsed, which this
    component reads via the parent x-data scope (railExpanded / mobileMenuOpen).
    localStorage sync is done here via an x-init watcher.
--}}
<div class="gw-sidebar flex flex-col h-full overflow-hidden"
     x-data="{
         /* Sync collapse to localStorage on every change */
         init() {
             this.$watch(() => $wire?.sidebarCollapsed, (v) => {
                 if (v !== undefined) {
                     try { localStorage.setItem('gw_sidebar_collapsed', v ? '1' : '0'); } catch(_) {}
                 }
             });
         },

         /* Arrow-key keyboard navigation across all [data-nav-item] anchors */
         navItems() {
             return Array.from(
                 this.$el.querySelectorAll('[data-nav-item]:not([disabled]):not([aria-hidden=true])')
             );
         },
         onKeyNav(e) {
             const items = this.navItems();
             const idx   = items.indexOf(document.activeElement);
             if (e.key === 'ArrowDown') {
                 e.preventDefault();
                 const next = items[idx + 1] ?? items[0];
                 next?.focus();
             } else if (e.key === 'ArrowUp') {
                 e.preventDefault();
                 const prev = items[idx - 1] ?? items[items.length - 1];
                 prev?.focus();
             } else if (e.key === 'Home') {
                 e.preventDefault(); items[0]?.focus();
             } else if (e.key === 'End') {
                 e.preventDefault(); items[items.length - 1]?.focus();
             }
         }
     }"
     @keydown="onKeyNav($event)"
     role="navigation"
     aria-label="Main navigation">

    {{-- ══════════════════════════════════════════════════════════════════════
         LOGO / TENANT BRANDING
    ══════════════════════════════════════════════════════════════════════════ --}}
    <div class="gw-sidebar-logo shrink-0 flex items-center overflow-hidden
                border-b border-[var(--divider)]"
         :class="railExpanded ? 'px-3 py-3 gap-2.5' : 'px-0 py-3 justify-center'"
         role="banner">

        <a href="{{ route('home') }}"
           wire:navigate
           data-nav-item
           class="flex items-center min-w-0 no-underline group rounded-md
                  focus-visible:outline-none focus-visible:ring-2
                  focus-visible:ring-[var(--tenant-primary)] focus-visible:ring-offset-1"
           :class="railExpanded ? 'gap-2.5' : 'justify-center'"
           x-tooltip.placement.right="!railExpanded ? '{{ addslashes($tenantName) }}' : false"
           aria-label="Go to dashboard — {{ $tenantName }}">

            {{-- Tenant avatar chip --}}
            <div class="w-8 h-8 shrink-0 rounded-[var(--radius-sm)]
                        flex items-center justify-center
                        text-white text-[0.75rem] font-extrabold
                        transition-transform duration-200 group-hover:scale-105"
                 style="background: linear-gradient(135deg, var(--tenant-primary), var(--tenant-primary-hover));"
                 aria-hidden="true">
                {{ $tenantInitial }}
            </div>

            {{-- Wordmark + domain (expanded only) --}}
            <div x-show="railExpanded" x-cloak class="flex flex-col min-w-0 leading-tight">
                <span class="text-[0.8125rem] font-semibold text-[var(--text-main)] truncate">
                    {{ $tenantName }}
                </span>
                @if ($tenantDomain)
                    <span class="text-[0.65rem] text-[var(--text-ghost)] truncate mt-0.5">
                        {{ $tenantDomain }}
                    </span>
                @endif
            </div>
        </a>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         SCROLLABLE NAV BODY
    ══════════════════════════════════════════════════════════════════════════ --}}
    <div class="gw-sidebar-body flex-1 min-h-0 overflow-y-auto overflow-x-hidden
                py-2 space-y-0"
         role="none"
         tabindex="-1">

        {{-- ─────────────────────────────────────────────────────────────────
             SECTION 1 — WORKSPACE
        ──────────────────────────────────────────────────────────────────── --}}
        <section aria-labelledby="nav-section-workspace">
            {{-- Section label (expanded only) --}}
            <div x-show="railExpanded" x-cloak class="px-4 pt-2 pb-1">
                <p id="nav-section-workspace"
                   class="gw-rail-section-label select-none">
                    Workspace
                </p>
            </div>

            <ul class="flex flex-col" role="list" aria-label="Workspace navigation">

                {{-- Dashboard --}}
                @php $isHome = Route::is('home'); @endphp
                <li role="none">
                    @include('layouts._sidebar-item', [
                        'href'     => route('home'),
                        'icon'     => 'fa-house-chimney',
                        'label'    => 'Dashboard',
                        'active'   => $isHome,
                        'tooltip'  => 'Dashboard',
                        'aria'     => $isHome ? 'aria-current="page"' : '',
                    ])
                </li>

                {{-- Documents --}}
                @if ($canDocs)
                    @php $isDocs = Route::is('documents.index'); @endphp
                    <li role="none">
                        @include('tenant.components.navigation.sidebar-item', [
                            'href'    => route('documents.index'),
                            'icon'    => 'fa-file-lines',
                            'label'   => 'Documents',
                            'active'  => $isDocs,
                            'tooltip' => 'Documents',
                            'aria'    => $isDocs ? 'aria-current="page"' : '',
                        ])
                    </li>
                @endif

                {{-- Tags --}}
                @if ($canTags)
                    @php $isTags = Route::is('tags.*'); @endphp
                    <li role="none">
                        @include('tenant.components.navigation.sidebar-item', [
                            'href'    => route('tags.index'),
                            'icon'    => 'fa-tags',
                            'label'   => 'Tags',
                            'active'  => $isTags,
                            'tooltip' => 'Tags',
                            'aria'    => $isTags ? 'aria-current="page"' : '',
                        ])
                    </li>
                @endif

                {{-- Shared Items --}}
                @if ($canShares)
                    @php $isShares = Route::is('shares.*'); @endphp
                    <li role="none">
                        @include('tenant.components.navigation.sidebar-item', [
                            'href'    => route('shares.index'),
                            'icon'    => 'fa-share-nodes',
                            'label'   => 'Shared',
                            'active'  => $isShares,
                            'tooltip' => 'Shared Items',
                            'aria'    => $isShares ? 'aria-current="page"' : '',
                        ])
                    </li>
                @endif

                {{-- Notifications --}}
                @if ($canNotif)
                    @php $isNotif = Route::is('notifications.*'); @endphp
                    <li role="none">
                        @include('tenant.components.navigation.sidebar-item', [
                            'href'    => '#',
                            'icon'    => 'fa-bell',
                            'label'   => 'Notifications',
                            'active'  => $isNotif ?? false,
                            'tooltip' => 'Notifications',
                            'aria'    => '',
                        ])
                    </li>
                @endif
            </ul>
        </section>

        {{-- ─────────────────────────────────────────────────────────────────
             SECTION 2 — ADMINISTRATION (role-gated)
        ──────────────────────────────────────────────────────────────────── --}}
        @if ($showAdmin)
            {{-- Soft divider between sections --}}
            <div class="mx-3 my-2" aria-hidden="true">
                <div class="h-px bg-[var(--divider)]"></div>
            </div>

            <section aria-labelledby="nav-section-admin">
                <div x-show="railExpanded" x-cloak class="px-4 pt-1 pb-1">
                    <p id="nav-section-admin"
                       class="gw-rail-section-label select-none">
                        Administration
                    </p>
                </div>

                <ul class="flex flex-col" role="list" aria-label="Administration navigation">

                    @if ($canUsers)
                        @php $isUsers = Route::is('users.*'); @endphp
                        <li role="none">
                            @include('tenant.components.navigation.sidebar-item', [
                                'href'    => route('users.index'),
                                'icon'    => 'fa-users',
                                'label'   => 'Users',
                                'active'  => $isUsers,
                                'tooltip' => 'Users',
                                'aria'    => $isUsers ? 'aria-current="page"' : '',
                            ])
                        </li>
                    @endif

                    @if ($canRoles)
                        @php $isRoles = Route::is('roles.*'); @endphp
                        <li role="none">
                            @include('tenant.components.navigation.sidebar-item', [
                                'href'    => route('roles.index'),
                                'icon'    => 'fa-shield-halved',
                                'label'   => 'Roles & Permissions',
                                'active'  => $isRoles,
                                'tooltip' => 'Roles & Permissions',
                                'aria'    => $isRoles ? 'aria-current="page"' : '',
                            ])
                        </li>
                    @endif

                    @if ($canProj)
                        @php $isProj = Route::is('projects.*'); @endphp
                        <li role="none">
                            @include('tenant.components.navigation.sidebar-item', [
                                'href'    => route('projects.index'),
                                'icon'    => 'fa-diagram-project',
                                'label'   => 'Projects',
                                'active'  => $isProj,
                                'tooltip' => 'Projects',
                                'aria'    => $isProj ? 'aria-current="page"' : '',
                            ])
                        </li>
                    @endif

                    @if ($canHrm)
                        @php $isHrm = Route::is('hrm.*'); @endphp
                        <li role="none">
                            @include('tenant.components.navigation.sidebar-item', [
                                'href'    => route('hrm.index'),
                                'icon'    => 'fa-id-card-clip',
                                'label'   => 'Human Resources',
                                'active'  => $isHrm,
                                'tooltip' => 'Human Resources',
                                'aria'    => $isHrm ? 'aria-current="page"' : '',
                            ])
                        </li>
                    @endif

                    @if ($canStats)
                        @php $isStats = Route::is('stats.*'); @endphp
                        <li role="none">
                            @include('tenant.components.navigation.sidebar-item', [
                                'href'    => route('stats.index'),
                                'icon'    => 'fa-chart-bar',
                                'label'   => 'Reports',
                                'active'  => $isStats,
                                'tooltip' => 'Reports',
                                'aria'    => $isStats ? 'aria-current="page"' : '',
                            ])
                        </li>
                    @endif
                </ul>
            </section>
        @endif

        {{-- ─────────────────────────────────────────────────────────────────
             SECTION 3 — FOLDER TREE (documents context only)
        ──────────────────────────────────────────────────────────────────── --}}
        @if ($canDocs && Route::is('documents.index'))
            <div x-show="railExpanded" x-cloak aria-label="Folder tree">
                <div class="mx-3 my-2" aria-hidden="true">
                    <div class="h-px bg-[var(--divider)]"></div>
                </div>
                <div class="px-3 pt-1 pb-1">
                    <p class="gw-rail-section-label select-none">Folders</p>
                </div>
                <div class="px-2 max-h-56 overflow-y-auto">
                    <livewire:documents.folder-tree />
                </div>
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         SIDEBAR FOOTER — Sign out · Settings · Collapse toggle
    ══════════════════════════════════════════════════════════════════════════ --}}
    <div class="gw-sidebar-footer shrink-0 pb-2
                border-t border-[var(--divider)]"
         role="group"
         aria-label="Account and navigation controls">

        {{-- Settings --}}
        @if ($authUser?->can('manage settings') || in_array('admin', $authUser?->getRoleNames()->toArray() ?? []))
            @php $isSettings = Route::is('settings.*'); @endphp
            <ul class="flex flex-col pt-1" role="list">
                <li role="none">
                    @include('tenant.components.navigation.sidebar-item', [
                        'href'    => '#',
                        'icon'    => 'fa-gear',
                        'label'   => 'Settings',
                        'active'  => $isSettings ?? false,
                        'tooltip' => 'Settings',
                        'aria'    => '',
                    ])
                </li>
            </ul>
        @endif

        {{-- Sign out --}}
        <div class="px-2 pt-1">
            <button type="button"
                    data-nav-item
                    @click="$refs.sidebarLogoutForm.submit()"
                    x-tooltip.placement.right="!railExpanded ? 'Sign out' : false"
                    class="gw-sidebar-item group flex items-center w-full
                           rounded-[var(--radius-sm)]
                           text-[0.8125rem] font-medium
                           text-[var(--danger-500)] hover:bg-[var(--danger-50)]
                           transition-colors duration-150 overflow-hidden
                           focus-visible:outline-none focus-visible:ring-2
                           focus-visible:ring-[var(--danger-500)] focus-visible:ring-offset-1"
                    :class="railExpanded ? 'gap-3 px-3 min-h-[36px]' : 'justify-center px-0 min-h-[40px]'"
                    aria-label="Sign out of {{ $authUser?->name ?? 'your account' }}">
                <i class="fas fa-arrow-right-from-bracket shrink-0 w-5 text-center text-[0.875rem]"
                   aria-hidden="true"></i>
                <span x-show="railExpanded" x-cloak class="whitespace-nowrap">Sign out</span>
            </button>
        </div>

        {{-- Hidden logout form --}}
        <form x-ref="sidebarLogoutForm"
              action="{{ route('logout') }}"
              method="POST"
              class="hidden"
              aria-hidden="true">
            @csrf
        </form>

        {{-- ── Collapse toggle button (desktop only) ── --}}
        <div class="px-2 pt-0.5">
            <button type="button"
                    class="gw-sidebar-item hidden lg:flex items-center w-full
                           rounded-[var(--radius-sm)]
                           text-[0.75rem] font-medium text-[var(--text-ghost)]
                           hover:bg-[var(--gw-surface-hover)] hover:text-[var(--text-muted)]
                           transition-colors duration-150 overflow-hidden
                           focus-visible:outline-none focus-visible:ring-2
                           focus-visible:ring-[var(--tenant-primary)] focus-visible:ring-offset-1"
                    :class="railExpanded
                        ? 'gap-3 px-3 min-h-[32px] justify-start'
                        : 'justify-center px-0 min-h-[40px]'"
                    @click="$wire.toggleSidebar()"
                    :aria-label="$wire.sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                    x-tooltip.placement.right="$wire.sidebarCollapsed ? 'Expand sidebar' : false">
                <i class="fas shrink-0 w-5 text-center text-[0.75rem]
                           transition-transform duration-300"
                   :class="$wire.sidebarCollapsed
                       ? 'fa-chevron-right'
                       : 'fa-chevron-left'"
                   aria-hidden="true"></i>
                <span x-show="railExpanded" x-cloak class="whitespace-nowrap">
                    Collapse
                </span>
            </button>
        </div>
    </div>
</div>