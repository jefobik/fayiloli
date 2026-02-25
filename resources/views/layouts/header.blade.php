<header class="edms-header" role="banner">

    {{-- ── Sidebar Toggle ─────────────────────────── --}}
    <button type="button" class="header-menu-btn" @click="sidebarOpen = !sidebarOpen"
        :aria-expanded="sidebarOpen.toString()" aria-controls="renderSidebarHtmlId"
        aria-label="Toggle sidebar navigation">
        <svg aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10" />
        </svg>
    </button>

    {{-- ── Brand (shown on mobile when sidebar closed) ──────── --}}
    <div class="header-brand" x-show="!sidebarOpen" aria-hidden="true">
        <a href="{{ route('home') }}" tabindex="-1"
            style="display:flex;align-items:center;gap:0.5rem;text-decoration:none">
            <img src="/img/fayiloli-icon.svg" alt="" aria-hidden="true" width="28" height="28"
                style="flex-shrink:0;border-radius:6px">
            <span class="app-title">Fayiloli v2.9</span>
        </a>
    </div>

    {{-- ── Global Search (MeiliSearch-powered Livewire) ──────── --}}
    @if (!Route::is('home'))
        <livewire:global-search />
    @endif

    {{-- ── Spacer ─────────────────────────────────── --}}
    <div style="flex:1" aria-hidden="true"></div>

    {{-- ── Header Actions ──────────────────────────── --}}
    <div class="header-actions" role="toolbar" aria-label="Header actions">

        {{-- Quick Upload --}}
        @if (Route::is('documents.index'))
            <button type="button" class="toolbar-btn toolbar-btn-primary" onclick="uploadFiles()"
                aria-label="Upload document">
                <i class="fas fa-upload" aria-hidden="true"></i>
                <span class="d-none d-md-inline">Upload</span>
            </button>
        @endif

        {{-- Dark mode toggle --}}
        <button type="button" class="header-icon-btn" id="darkModeToggle" aria-label="Toggle dark mode"
            aria-pressed="false" onclick="edmsDarkModeToggle(this)">
            <i class="fas fa-moon" id="darkModeIcon" aria-hidden="true" style="font-size:1rem"></i>
        </button>

        {{-- Config dropdown --}}
        <div class="relative" x-data="{ open: false }" @click.outside="open = false"
            @keydown.escape.window="open = false">
            <button type="button" class="header-icon-btn" id="configMenuBtn" @click="open = !open"
                :aria-expanded="open.toString()" aria-haspopup="true" aria-controls="configDropdownMenu"
                aria-label="Configuration menu">
                <i class="fas fa-cog" aria-hidden="true" style="font-size:1rem"></i>
            </button>
            <div class="edms-dropdown" id="configDropdownMenu" x-show="open" x-cloak role="menu"
                aria-label="Configuration options" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95" style="min-width:180px">
                <a class="edms-dropdown-item" role="menuitem" href="{{ route('documents.index') }}">
                    <i class="fas fa-file-alt" aria-hidden="true"></i> Documents
                </a>
                <a class="edms-dropdown-item" role="menuitem" href="{{ route('folders.index') }}">
                    <i class="fas fa-layer-group" aria-hidden="true"></i> Workspaces
                </a>
                <a class="edms-dropdown-item" role="menuitem" href="{{ route('tags.index') }}">
                    <i class="fas fa-tags" aria-hidden="true"></i> Tags
                </a>
                <div class="edms-dropdown-divider" role="separator"></div>
                <a class="edms-dropdown-item" role="menuitem" href="{{ route('home') }}">
                    <i class="fas fa-chart-bar" aria-hidden="true"></i> Dashboard
                </a>
            </div>
        </div>

        {{-- Tenant Switcher --}}
        @php
            $currentTenant = tenancy()->initialized ? tenancy()->tenant : null;
            $portalUrl = rtrim(config('app.url'), '/') . '/portal';
        @endphp
        @if($currentTenant)
            <div class="relative" x-data="{ open: false }" @click.outside="open = false"
                @keydown.escape.window="open = false">
                <button type="button"
                    class="header-icon-btn flex items-center justify-center gap-2 px-3 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 transition-colors"
                    id="tenantSwitcherBtn" style="width:auto; border-radius:8px;" @click="open = !open"
                    :aria-expanded="open.toString()" aria-haspopup="true" aria-controls="tenantDropdownMenu"
                    aria-label="Switch Workspace. Current: {{ $currentTenant->organization_name }}">
                    <i class="fas fa-building" aria-hidden="true"></i>
                    <span class="hidden md:inline font-semibold text-[0.85rem] max-w-[150px] truncate">
                        {{ $currentTenant->organization_name }}
                    </span>
                    <i class="fas fa-chevron-down text-[0.6rem] opacity-70 ml-1" aria-hidden="true"></i>
                </button>

                <div class="edms-dropdown" id="tenantDropdownMenu" x-show="open" x-cloak role="menu"
                    aria-label="Workspace switch options" x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95" style="min-width:260px; right:0">

                    <div class="px-4 py-3 border-b flex flex-col items-start border-slate-100 dark:border-slate-700"
                        aria-hidden="true">
                        <div
                            class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest leading-none">
                            Current Workspace</div>
                        <div class="text-sm font-bold text-slate-900 dark:text-white mt-1.5 leading-tight">
                            {{ $currentTenant->organization_name }}</div>
                    </div>

                    <a class="edms-dropdown-item group flex flex-row items-center gap-3 !px-4 !py-3 hover:!bg-indigo-50 dark:hover:!bg-indigo-900/20"
                        role="menuitem" href="{{ $portalUrl }}">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 shrink-0 group-hover:scale-105 transition-transform"
                            aria-hidden="true">
                            <i class="fas fa-arrow-right-arrow-left"></i>
                        </div>
                        <div class="flex flex-col items-start justify-center">
                            <div class="text-[0.85rem] font-bold text-indigo-700 dark:text-indigo-300 leading-none">Switch
                                Workspace</div>
                            <div class="text-[0.7rem] font-medium text-indigo-500 dark:text-indigo-400 mt-1 leading-none">Go
                                to central portal</div>
                        </div>
                    </a>
                </div>
            </div>
        @endif

        {{-- Notification Bell (Livewire) --}}
        <livewire:notification-bell />

        {{-- User Menu --}}
        <div class="relative" x-data="{ open: false }" @click.outside="open = false"
            @keydown.escape.window="open = false">
            <button type="button" class="header-user" id="userMenuBtn" @click="open = !open"
                :aria-expanded="open.toString()" aria-haspopup="true" aria-controls="userDropdownMenu"
                aria-label="User menu for {{ Auth::user()?->name }}">
                <div aria-hidden="true">
                    <div class="user-name">{{ Auth::user()?->name }}</div>
                    <div class="user-role">
                        @if(Auth::user()?->getRoleNames()->isNotEmpty())
                            {{ Auth::user()->getRoleNames()->first() }}
                        @else
                            Member
                        @endif
                    </div>
                </div>
                <div class="avatar" aria-hidden="true">
                    {{ strtoupper(substr(Auth::user()?->name ?? 'U', 0, 1)) }}{{ strtoupper(substr(explode(' ', Auth::user()?->name ?? 'U ')[1] ?? '', 0, 1)) }}
                </div>
            </button>

            <div class="edms-dropdown" id="userDropdownMenu" x-show="open" x-cloak role="menu"
                aria-label="User account options" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                style="right:0;min-width:200px">

                {{-- User info header --}}
                <div style="padding:0.9rem 1rem;border-bottom:1px solid #f3f4f6" aria-hidden="true">
                    <div style="font-size:0.875rem;font-weight:700;color:#1e293b">{{ Auth::user()?->name }}</div>
                    <div style="font-size:0.75rem;color:#64748b;margin-top:0.1rem">{{ Auth::user()?->email }}</div>
                    @if(Auth::user()?->getRoleNames()->isNotEmpty())
                        <span class="role-badge role-{{ Auth::user()->getRoleNames()->first() }}" style="margin-top:0.4rem">
                            {{ Auth::user()->getRoleNames()->first() }}
                        </span>
                    @endif
                </div>

                <a class="edms-dropdown-item" role="menuitem" href="{{ route('users.show', Auth::id()) }}">
                    <i class="fas fa-user-circle" aria-hidden="true"></i> Profile
                </a>
                <a class="edms-dropdown-item" role="menuitem" href="{{ route('home') }}">
                    <i class="fas fa-tachometer-alt" aria-hidden="true"></i> Dashboard
                </a>
                <div class="edms-dropdown-divider" role="separator"></div>
                <a class="edms-dropdown-item" role="menuitem" href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit()"
                    style="color:#dc2626">
                    <i class="fas fa-sign-out-alt" aria-hidden="true" style="color:#dc2626"></i> Sign out
                </a>
            </div>
        </div>
    </div>

    {{-- Logout form --}}
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none" aria-hidden="true">
        @csrf
    </form>

</header>

{{-- Update dark-mode toggle aria-pressed on page load --}}
<script>
    (function () {
        document.addEventListener('DOMContentLoaded', function () {
            var btn = document.getElementById('darkModeToggle');
            if (btn && localStorage.getItem('darkMode') === 'true') {
                btn.setAttribute('aria-pressed', 'true');
            }
        });
    })();

    // Override edmsDarkModeToggle to also update aria-pressed
    function edmsDarkModeToggle(btn) {
        var isDark = document.body.classList.toggle('dark-mode');
        localStorage.setItem('darkMode', isDark);
        var icon = document.getElementById('darkModeIcon');
        if (icon) {
            icon.classList.toggle('fa-moon', !isDark);
            icon.classList.toggle('fa-sun', isDark);
        }
        if (btn) btn.setAttribute('aria-pressed', isDark.toString());
    }
</script>