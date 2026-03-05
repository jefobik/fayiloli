{{--
╔══════════════════════════════════════════════════════════════════════╗
║ GW-STYLE APP SHELL — Enterprise SaaS Edition ║
║ Architecture: ║
║ • 64px topbar → position:fixed z:1080 ║
║ • 72/240px rail → position:fixed z:1070 top:64px ║
║ • Canvas → margin-top:64px, margin-left:dynamic ║
║ Features: ║
║ • data-sidebar-collapsed attribute for CSS child targeting ║
║ • Mouse-enter debounce (150ms) to prevent rail flash ║
║ • Escape key closes mobile drawer ║
║ • localStorage init syncs sidebarCollapsed before first render ║
║ • <footer> landmark ║
    ╚══════════════════════════════════════════════════════════════════════╝
    --}}
    <div class="gw-shell h-full" id="appShell" x-data="{
         mobileMenuOpen: false,
         sidebarHovered: false,
         _hoverTimer:    null,

         get railExpanded() {
             return !$wire.sidebarCollapsed || this.sidebarHovered || this.mobileMenuOpen;
         },

         /* Read localStorage BEFORE first render to avoid FOUC on rail width */
         init() {
             try {
                 const stored = localStorage.getItem('gw_sidebar_collapsed');
                 if (stored !== null) {
                     const shouldCollapse = stored === '1';
                     if ($wire.sidebarCollapsed !== shouldCollapse) {
                         $wire.sidebarCollapsed = shouldCollapse;
                     }
                 }
             } catch(_) {}
         },

         onRailEnter() {
             clearTimeout(this._hoverTimer);
             this._hoverTimer = setTimeout(() => { this.sidebarHovered = true; }, 150);
         },
         onRailLeave() {
             clearTimeout(this._hoverTimer);
             this.sidebarHovered = false;
         },
     }" :data-sidebar-collapsed="$wire.sidebarCollapsed ? 'true' : null">

        {{-- ── HEADER (fixed, always on top) ───────────────────────────────────── --}}
        @include('tenant.components.layout.header')

        {{-- ── MOBILE DRAWER BACKDROP ─────────────────────────────────────────── --}}
        <div x-show="mobileMenuOpen" x-transition:enter="transition-opacity ease-linear duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-[1060] bg-black/40 backdrop-blur-sm lg:hidden"
            @click="mobileMenuOpen = false" aria-hidden="true">
        </div>

        {{-- ── MOBILE DRAWER ───────────────────────────────────────────────────── --}}
        <aside id="mobile-nav-drawer" x-show="mobileMenuOpen"
            x-transition:enter="transition ease-in-out duration-250 transform"
            x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in-out duration-200 transform" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full" @keydown.escape.window="mobileMenuOpen = false" class="fixed inset-y-0 left-0 z-[1065] flex flex-col
                  w-[var(--gw-rail-width-full)]
                  bg-[var(--gw-rail-bg,var(--panel-bg))]
                  border-r border-[var(--gw-rail-border,var(--panel-border))]
                  overflow-y-auto overflow-x-hidden pt-3 pb-6 shadow-xl lg:hidden" role="dialog" aria-modal="true"
            aria-label="Mobile navigation">

            {{-- Close button --}}
            <div class="flex items-center justify-end px-3 pb-2">
                <button type="button" @click="mobileMenuOpen = false" class="flex items-center justify-center w-9 h-9 rounded-full
                           text-[var(--text-muted)] hover:bg-[var(--gw-surface-hover)]
                           transition-colors" aria-label="Close navigation">
                    <i class="fas fa-times text-base" aria-hidden="true"></i>
                </button>
            </div>

            @include('tenant.components.navigation.sidebar')
        </aside>

        {{-- ── DESKTOP RAIL ────────────────────────────────────────────────────── --}}
        <aside class="hidden lg:flex flex-col fixed inset-y-0 left-0 z-[1070]
                  top-[var(--gw-header-height)] bottom-0
                  bg-[var(--gw-rail-bg,var(--panel-bg))]
                  border-r border-[var(--gw-rail-border,var(--panel-border))]
                  overflow-y-auto overflow-x-hidden
                  transition-[width] duration-300 ease-in-out pt-3 pb-4" :class="railExpanded
              ? 'w-[var(--gw-rail-width-full)]'
              : 'w-[var(--gw-rail-width-compact)]'" @mouseenter="onRailEnter()" @mouseleave="onRailLeave()"
            aria-label="Primary navigation">
            @include('tenant.components.navigation.sidebar')
        </aside>

        {{-- ── MAIN CANVAS ─────────────────────────────────────────────────────── --}}
        <div class="flex flex-col min-h-screen
                transition-[padding-left] duration-300 ease-in-out
                pt-[var(--gw-header-height)]" :class="$wire.sidebarCollapsed
             ? 'lg:pl-[var(--gw-rail-width-compact)]'
             : 'lg:pl-[var(--gw-rail-width-full)]'">

            <main id="main-content" tabindex="-1" class="flex-1 flex flex-col min-w-0 bg-[var(--app-bg)]"
                aria-label="Main content">
                <div class="flex-1 overflow-y-auto
                        px-4 sm:px-6 lg:px-8 py-6">
                    <div class="max-w-[var(--gw-content-max-width,100rem)] mx-auto">
                        {{ $slot }}
                    </div>
                </div>
            </main>

            {{-- Global TallStackUI overlays --}}
            <x-ts-toast />
            <x-ts-dialog />

            {{-- Footer --}}
            <footer class="hidden lg:flex items-center justify-end
                       px-6 py-2 gap-2
                       text-[0.7rem] text-[var(--text-ghost)] select-none" aria-label="Application footer">
                <span>{{ config('app.name', 'Fayiloli') }}</span>
                <span aria-hidden="true">·</span>
                <span>{{ date('Y') }}</span>
            </footer>
        </div>
    </div>