{{--
╔═══════════════════════════════════════════════════════════════════════╗
║ GW-STYLE TOP APP BAR v5.0 — Enterprise SaaS Edition ║
║ ║
║ Layout (3 zones): ║
║ LEFT : [☰ Hamburger] [Avatar+TenantName] [TenantBadge] ║
║ CENTRE: [────────────── Global Omnibox (⌘K) ──────────────] ║
║ RIGHT : [ThemeToggle] [WS Switcher] [Bell] [Avatar Menu] ║
║ ║
║ Spec: ║
║ Height: 64px (var(--gw-header-height)) ║
║ Surface: var(--gw-topbar-bg) — light white / dark-mode aware ║
║ Border: 1px bottom, var(--divider) — soft, no shadow ║
║ Sticky: position:fixed z:1080 ║
║ All icon buttons: 40px circle touch targets ║
╚═══════════════════════════════════════════════════════════════════════╝
--}}

@php
    $authUser = Auth::user();
    $tn = tenancy()->initialized ? tenancy()->tenant : null;
    $tenantName = $tn?->short_name ?? $tn?->organization_name ?? config('app.name', 'Fayiloli');
    $tenantInit = strtoupper(substr($tenantName, 0, 1));
    $userInit = strtoupper(substr($authUser?->name ?? 'U', 0, 1));
    $userRoles = $authUser?->getRoleNames()->toArray() ?? [];
    $primaryRole = ucfirst($userRoles[0] ?? 'Member');
@endphp

<header id="gw-topbar" class="fixed top-0 left-0 right-0 z-[var(--z-header,1080)]
               flex items-center gap-2
               h-[var(--gw-header-height,64px)]
               bg-[var(--gw-topbar-bg,var(--panel-bg))]
               border-b border-[var(--divider)]
               px-2 sm:px-3
               select-none" style="padding-top: env(safe-area-inset-top);" role="banner">

    {{-- ════════════════════════════════════════════════════════════════════
    LEFT ZONE — Mobile hamburger + Tenant identity
    ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="flex items-center gap-2 shrink-0 min-w-0">

        {{-- Mobile hamburger --}}
        <button type="button" class="lg:hidden gw-topbar-btn" @click="mobileMenuOpen = true"
            aria-label="Open navigation" aria-controls="mobile-nav-drawer" :aria-expanded="mobileMenuOpen.toString()">
            <i class="fas fa-bars text-base" aria-hidden="true"></i>
        </button>

        {{-- Tenant avatar + name (desktop) --}}
        <a href="{{ route('home') }}" wire:navigate class="hidden lg:flex items-center gap-2 min-w-0 no-underline
                  rounded-[var(--radius-sm)] px-2 py-1 -mx-1
                  hover:bg-[var(--gw-surface-hover)] transition-colors
                  focus-visible:outline-none focus-visible:ring-2
                  focus-visible:ring-[var(--tenant-primary)]" aria-label="Home — {{ $tenantName }}">

            {{-- Tenant avatar chip --}}
            <div class="w-7 h-7 rounded-[var(--radius-xs)] shrink-0
                        flex items-center justify-center
                        text-white text-[0.6875rem] font-extrabold"
                style="background: linear-gradient(135deg, var(--tenant-primary), var(--tenant-primary-hover));"
                aria-hidden="true">
                {{ $tenantInit }}
            </div>

            {{-- Tenant org name + badge (visible xl+) --}}
            <span class="hidden xl:flex items-center gap-1.5 min-w-0">
                <span class="text-[0.8125rem] font-semibold text-[var(--text-main)] truncate max-w-[140px]">
                    {{ $tenantName }}
                </span>
            </span>
        </a>

        {{-- Tenant subscription/plan badge chip (desktop only) --}}
        @if($tn)
            <span class="hidden lg:inline-flex items-center gap-1
                                 h-5 px-2 rounded-full
                                 text-[0.6rem] font-bold tracking-[0.06em] uppercase
                                 bg-[var(--tenant-primary-muted)] text-[var(--tenant-primary)]
                                 border border-[var(--tenant-primary)]/20
                                 whitespace-nowrap select-none shrink-0"
                aria-label="Workspace: {{ $tn?->plan ?? 'Enterprise' }}">
                {{ $tn->plan_label ?? 'Enterprise' }}
            </span>
        @endif
    </div>

    {{-- ════════════════════════════════════════════════════════════════════
    CENTRE ZONE — Global search (always visible, not just outside home)
    ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="flex-1 flex items-center justify-center px-2 sm:px-4 max-w-[640px] mx-auto w-full">
        <div class="relative w-full group">

            {{-- Search icon --}}
            <div class="absolute left-3 top-1/2 -translate-y-1/2 z-10
                        text-[var(--text-ghost)] group-focus-within:text-[var(--gw-blue-600)]
                        transition-colors pointer-events-none" aria-hidden="true">
                <i class="fas fa-search text-[0.8125rem]"></i>
            </div>

            {{-- Livewire Omnibox --}}
            <div class="gw-search-bar-wrap">
                <livewire:global-search />
            </div>

            {{-- ⌘K kbd hint (hidden on mobile, hidden when input focused) --}}
            <kbd class="absolute right-3 top-1/2 -translate-y-1/2
                        hidden sm:inline-flex items-center gap-1
                        px-1.5 py-0.5 text-[0.6rem] font-semibold tracking-wider
                        text-[var(--text-ghost)]
                        border border-[var(--divider)]
                        rounded-[var(--radius-xs)]
                        bg-[var(--gw-surface-2)]
                        pointer-events-none
                        group-focus-within:opacity-0 transition-opacity" aria-label="Keyboard shortcut: Command K"
                title="Press ⌘K or Ctrl+K to search">
                ⌘K
            </kbd>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════
    RIGHT ZONE — Toolbar actions
    ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="flex items-center gap-0.5 shrink-0" role="toolbar" aria-label="Topbar actions">

        {{-- Upload / New action (context-sensitive: docs page + permission) --}}
        @if (Route::is('documents.index') && $authUser?->can('create documents'))
            <button type="button" onclick="uploadFiles()" class="hidden sm:inline-flex items-center gap-1.5
                                   h-8 px-3.5 rounded-[var(--radius-pill)]
                                   bg-[var(--gw-blue-50)] text-[var(--gw-blue-600)]
                                   border border-[var(--gw-blue-200)]
                                   text-[0.8125rem] font-semibold
                                   hover:bg-[var(--gw-blue-100)]
                                   transition-colors" aria-label="Upload new document">
                <i class="fas fa-plus text-[0.6rem]" aria-hidden="true"></i>
                New
            </button>
            <button type="button" onclick="uploadFiles()" class="sm:hidden gw-topbar-btn text-[var(--gw-blue-600)]"
                aria-label="Upload document">
                <i class="fas fa-plus text-base" aria-hidden="true"></i>
            </button>
        @endif

        {{-- ── Theme Toggle ────────────────────────────────────────────── --}}
        {{-- Cycles themes by dispatching 'cycle-theme' window event. --}}
        {{-- GlobalThemeSwitcher (@cycle-theme.window) bridges it to --}}
        {{-- $wire.updateTheme() for DB persistence + full applyTheme() call. --}}
        <button type="button" class="gw-topbar-btn hidden sm:flex items-center justify-center" @click="
                    var themes = ['system','light','dark'];
                    var cur = window.__themePreference || 'system';
                    var next = themes[(themes.indexOf(cur) + 1) % themes.length];
                    window.__themePreference = next;
                    window.dispatchEvent(new CustomEvent('cycle-theme', { detail: { nextTheme: next } }));
                " :aria-label="'Appearance: ' + (window.__themePreference || 'system') + '. Click to cycle'"
            x-tooltip.placement.bottom="'Appearance: ' + (window.__themePreference || 'system')">
            <i class="fas text-base transition-all" :class="{
                   'fa-sun':                window.__themePreference === 'light',
                   'fa-moon':               window.__themePreference === 'dark',
                   'fa-circle-half-stroke': !window.__themePreference || window.__themePreference === 'system'
               }" aria-hidden="true"></i>
        </button>

        {{-- Soft vertical separator --}}
        <div class="hidden md:block h-4 w-px bg-[var(--divider)] mx-1" aria-hidden="true"></div>

        {{-- Workspace Switcher (tenant domain only) --}}
        @if(tenancy()->initialized)
            <livewire:layouts.workspace-switcher />
        @endif

        {{-- Notification Bell --}}
        <div aria-live="polite" aria-atomic="true" aria-label="Notifications">
            <livewire:notification-bell />
        </div>

        {{-- Soft vertical separator --}}
        <div class="hidden md:block h-4 w-px bg-[var(--divider)] mx-1" aria-hidden="true"></div>

        {{-- ── User Avatar + Dropdown ─────────────────────────────────── --}}
        <div x-data="{ open: false }" class="relative" @keydown.escape.window="open = false">

            {{-- Avatar trigger --}}
            <button type="button" class="relative flex items-center justify-center w-9 h-9 rounded-full
                           ring-2 transition-all duration-200
                           focus-visible:outline-none focus-visible:ring-[var(--tenant-primary)]" :class="open
                        ? 'ring-[var(--tenant-primary)]'
                        : 'ring-transparent hover:ring-[var(--tenant-primary)]/40'" @click="open = !open"
                :aria-expanded="open.toString()" aria-haspopup="true"
                aria-label="Account menu — {{ $authUser?->name }}">

                <div class="w-8 h-8 rounded-full flex items-center justify-center
                             text-white text-[0.75rem] font-bold"
                    style="background: linear-gradient(135deg, var(--tenant-primary), var(--tenant-primary-hover));">
                    {{ $userInit }}
                </div>

                {{-- Online indicator dot --}}
                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full
                             bg-[var(--success-500)] border-2 border-[var(--gw-topbar-bg,var(--panel-bg))]"
                    aria-hidden="true"></span>
            </button>

            {{-- ── Dropdown Panel ──────────────────────────────────── --}}
            <div x-show="open" x-cloak @click.outside="open = false"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95 translate-y-1" class="absolute top-[calc(100%+8px)] right-0
                        w-[280px]
                        bg-[var(--panel-bg)]
                        border border-[var(--panel-border)]
                        rounded-[var(--radius-md)]
                        shadow-[var(--elevation-3)]
                        overflow-hidden
                        z-[var(--z-dropdown,1090)]" role="menu" aria-label="User account options">

                {{-- ── Identity block ── --}}
                <div class="flex items-center gap-3 px-4 py-3.5
                             border-b border-[var(--divider)]">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center
                                 text-white text-sm font-bold shrink-0"
                        style="background: linear-gradient(135deg, var(--tenant-primary), var(--tenant-primary-hover));"
                        aria-hidden="true">
                        {{ $userInit }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[0.8125rem] font-semibold text-[var(--text-main)] truncate">
                            {{ $authUser?->name }}
                        </p>
                        <p class="text-[0.75rem] text-[var(--text-muted)] truncate mt-0.5">
                            {{ $authUser?->email }}
                        </p>
                        {{-- Role badge --}}
                        <span class="inline-flex items-center mt-1
                                     h-4 px-1.5 rounded-full
                                     text-[0.6rem] font-bold tracking-[0.05em] uppercase
                                     bg-[var(--gw-surface-2)] text-[var(--text-ghost)]">
                            {{ $primaryRole }}
                        </span>
                    </div>
                </div>

                {{-- ── Tenant context ── --}}
                @if($tn)
                    <div class="px-4 py-2.5 border-b border-[var(--divider)]
                                        flex items-center gap-2">
                        <div class="w-6 h-6 rounded-[var(--radius-xs)] shrink-0
                                            flex items-center justify-center
                                            text-white text-[0.625rem] font-extrabold"
                            style="background: linear-gradient(135deg, var(--tenant-primary), var(--tenant-primary-hover));"
                            aria-hidden="true">
                            {{ $tenantInit }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[0.75rem] font-medium text-[var(--text-main)] truncate">
                                {{ $tenantName }}
                            </p>
                            @php $dom = $tn?->domains?->first()?->domain ?? null; @endphp
                            @if($dom)
                                <p class="text-[0.7rem] text-[var(--text-ghost)] truncate">
                                    {{ $dom }}
                                </p>
                            @endif
                        </div>
                        <span class="inline-flex items-center
                                             h-4 px-1.5 rounded-full
                                             text-[0.6rem] font-bold tracking-[0.05em] uppercase
                                             bg-[var(--tenant-primary-muted)] text-[var(--tenant-primary)]
                                             shrink-0">
                            {{ $tn->plan_label ?? 'Pro' }}
                        </span>
                    </div>
                @endif

                {{-- ── Theme switcher row ── --}}
                <div class="px-3 py-2.5 border-b border-[var(--divider)]">
                    <livewire:global-theme-switcher />
                </div>

                {{-- ── Navigation links ── --}}
                <div role="none" class="py-1">
                    <a href="{{ route('users.show', Auth::id()) }}" class="flex items-center gap-3 px-4 py-2.5
                              text-[0.8125rem] text-[var(--text-main)]
                              hover:bg-[var(--gw-surface-hover)]
                              transition-colors no-underline" role="menuitem">
                        <i class="fas fa-circle-user w-4 text-center text-[var(--text-ghost)]" aria-hidden="true"></i>
                        My profile
                    </a>
                    <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-3 px-4 py-2.5
                              text-[0.8125rem] text-[var(--text-main)]
                              hover:bg-[var(--gw-surface-hover)]
                              transition-colors no-underline" role="menuitem">
                        <i class="fas fa-house-chimney w-4 text-center text-[var(--text-ghost)]" aria-hidden="true"></i>
                        Dashboard
                    </a>
                </div>

                {{-- ── Sign out ── --}}
                <div class="border-t border-[var(--divider)] py-1" role="none">
                    {{--
                    Fix #4 — aria-label now mentions the shortcut for screen readers.
                    aria-keyshortcuts declares it per ARIA 1.1 spec.
                    Fix #7 — $refs.headerLogoutForm now targets the sidebar's canonical
                    #logout-form via the x-ref alias (both are the same form because
                    the header shares the parent Alpine scope via Livewire).
                    The hidden <form> below has been removed — sidebar.blade.php
                        owns the single canonical form (id="logout-form").
                        --}}
                        <button type="button" class="group flex items-center gap-3 w-full px-4 py-2.5 text-left
                                   text-[0.8125rem] text-[var(--danger-500)]
                                   hover:bg-[var(--danger-50)]
                                   transition-colors
                                   focus-visible:outline-none focus-visible:ring-2
                                   focus-visible:ring-inset focus-visible:ring-[var(--danger-500)]"
                            @click="document.getElementById('logout-form')?.submit()" role="menuitem"
                            aria-label="Sign out (keyboard shortcut: ⌘⇧Q)"
                            aria-keyshortcuts="Meta+Shift+Q Control+Shift+Q">
                            <i class="fas fa-arrow-right-from-bracket w-4 text-center
                                   text-[var(--danger-500)] transition-colors" aria-hidden="true"></i>
                            <span>Sign out</span>
                            <span class="ml-auto text-[0.65rem] text-[var(--text-ghost)]
                                     font-mono hidden sm:block" aria-hidden="true">⌘⇧Q</span>
                        </button>
                </div>
            </div>
        </div>
    </div>
</header>