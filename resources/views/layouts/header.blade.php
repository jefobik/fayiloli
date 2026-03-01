<header
    class="sticky top-0 z-40 flex w-full min-h-[64px] shrink-0 items-center justify-between gap-x-4 border-b border-[var(--panel-border)] bg-[var(--panel-bg)]/80 supports-backdrop-blur:bg-[var(--panel-bg)]/80 backdrop-blur-md px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8 transition-colors pt-[env(safe-area-inset-top)]"
    role="banner">

    <div class="flex items-center gap-4">
        {{-- ── Sidebar Toggle ─────────────────────────── --}}
        <button type="button"
            class="-m-2.5 p-2.5 min-h-[44px] min-w-[44px] flex items-center justify-center text-[var(--text-muted)] lg:hidden hover:bg-[var(--slate-100)] dark:hover:bg-white/5 rounded-md transition-colors"
            @click="mobileMenuOpen = true" aria-label="Open sidebar">
            <span class="sr-only">Open sidebar</span>
            <x-ts-icon name="bars-3" class="h-6 w-6" />
        </button>

        {{-- ── Brand (shown on mobile when sidebar closed) ──────── --}}
        <div class="lg:hidden flex items-center" aria-hidden="true">
            <a href="{{ route('home') }}" tabindex="-1" wire:navigate
                class="flex items-center gap-2 text-[var(--tenant-primary)] no-underline">
                <x-ts-icon name="presentation-chart-line" class="w-8 h-8 shrink-0" />
                <span class="font-bold text-lg tracking-tight text-[var(--text-main)]">
                    {{ tenancy()->initialized ? (tenancy()->tenant->short_name ?? 'Ostrich') : 'Ostrich' }}
                </span>
            </a>
        </div>
    </div>

    {{-- ── Global Search ──────── --}}
    @if (!Route::is('home'))
        <div class="flex items-center gap-2">
            <livewire:global-search />
            <kbd class="kbd-shortcut hidden lg:inline-flex border-[var(--panel-border)] text-[var(--text-muted)]"
                title="Keyboard shortcut: Ctrl+K or Cmd+K">⌘K</kbd>
        </div>
    @endif

    {{-- ── Spacer ─────────────────────────────────── --}}
    <div style="flex:1" aria-hidden="true"></div>

    {{-- ── Header Actions ──────────────────────────── --}}
    <div class="flex items-center gap-x-2 sm:gap-x-4 lg:gap-x-6" role="toolbar" aria-label="Header actions">

        {{-- Workspace Switcher --}}
        @if(tenancy()->initialized)
            <livewire:layouts.workspace-switcher />
        @endif

        {{-- Quick Upload --}}
        @if (Route::is('documents.index'))
            <x-ts-button color="primary" icon="arrow-up-tray" position="left" onclick="uploadFiles()"
                aria-label="Upload document" class="hidden sm:inline-flex shadow-sm hover:shadow-md transition-all">
                Upload
            </x-ts-button>
            <x-ts-button color="primary" icon="arrow-up-tray" position="left" onclick="uploadFiles()"
                aria-label="Upload document"
                class="sm:hidden w-10 h-10 !px-0 flex items-center justify-center rounded-full shadow-sm">
            </x-ts-button>
        @endif

        {{-- Separator --}}
        <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-[var(--panel-border)]" aria-hidden="true"></div>

        {{-- Notification Bell --}}
        <livewire:notification-bell />

        {{-- User Menu --}}
        <x-ts-dropdown position="bottom-end">
            <x-slot:action>
                <button type="button"
                    class="flex items-center gap-x-3 p-1 rounded-full min-h-[44px] hover:bg-[var(--slate-100)] dark:hover:bg-white/5 transition-colors focus:ring-2 focus:ring-[var(--tenant-primary)] focus:outline-none"
                    aria-label="User menu for {{ Auth::user()?->name }}">
                    <span class="hidden lg:flex lg:items-center">
                        <span class="text-sm font-bold leading-6 text-[var(--text-main)]"
                            aria-hidden="true">{{ Auth::user()?->name }}</span>
                    </span>
                    <div class="flex items-center justify-center w-8 h-8 bg-[var(--tenant-primary)] text-white rounded-full text-xs font-bold shrink-0 shadow-sm"
                        aria-hidden="true">
                        {{ strtoupper(substr(Auth::user()?->name ?? 'U', 0, 1)) }}
                    </div>
                </button>
            </x-slot:action>

            <div class="px-4 py-3 border-b border-[var(--panel-border)]">
                <p class="text-sm font-bold text-[var(--text-main)] truncate">{{ Auth::user()?->name }}</p>
                <p class="text-xs text-[var(--text-muted)] truncate mt-0.5">{{ Auth::user()?->email }}</p>
            </div>

            <div class="px-3 py-2 border-b border-[var(--panel-border)]">
                <livewire:global-theme-switcher />
            </div>

            <x-ts-dropdown.items icon="user-circle" text="Profile" href="{{ route('users.show', Auth::id()) }}" />
            <x-ts-dropdown.items icon="chart-bar" text="Dashboard" href="{{ route('home') }}" />
            <x-ts-dropdown.items separator />
            <x-ts-dropdown.items icon="arrow-right-start-on-rectangle" text="Sign out" color="red"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit()" />
        </x-ts-dropdown>
    </div>

    {{-- Logout form --}}
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden" aria-hidden="true">
        @csrf
    </form>

</header>

<script>
    // Cmd+K / Ctrl+K → focus global search input
    document.addEventListener('keydown', function (e) {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            var el = document.querySelector('input[type="search"], [data-search]');
            if (el) { el.focus(); el.select(); }
        }
    });
</script>