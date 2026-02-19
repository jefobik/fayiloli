<header class="edms-header">

    {{-- ── Sidebar Toggle ─────────────────────────── --}}
    <button class="header-menu-btn" @click="sidebarOpen = !sidebarOpen" title="Toggle sidebar">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10"/>
        </svg>
    </button>

    {{-- ── Brand (shown on mobile when sidebar closed) ──────── --}}
    <div class="header-brand" x-show="!sidebarOpen">
        <a href="{{ route('home') }}" style="display:flex;align-items:center;gap:0.5rem;text-decoration:none">
            <svg style="width:28px;height:28px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path fill="#7c3aed" d="M512 256a15 15 0 00-7.1-12.8l-52-32 52-32.5a15 15 0 000-25.4L264 2.3c-4.8-3-11-3-15.9 0L7 153.3a15 15 0 000 25.4L58.9 211 7.1 243.3a15 15 0 000 25.4L58.8 301 7.1 333.3a15 15 0 000 25.4l241 151a15 15 0 0015.9 0l241-151a15 15 0 00-.1-25.5l-52-32 52-32.5A15 15 0 00512 256z"/>
            </svg>
            <span class="app-title">Fayiloli v2.9</span>
        </a>
    </div>

    {{-- ── Global Search (MeiliSearch-powered Livewire) ──────── --}}
    @if (!Route::is('home'))
        <livewire:global-search />
    @endif

    {{-- ── Spacer ─────────────────────────────────── --}}
    <div style="flex:1"></div>

    {{-- ── Header Actions ──────────────────────────── --}}
    <div class="header-actions">

        {{-- Quick Upload --}}
        @if (Route::is('documents.index'))
            <button class="toolbar-btn toolbar-btn-primary" onclick="uploadFiles()" title="Upload document">
                <i class="fas fa-upload"></i>
                <span style="display:none" class="d-md-inline">Upload</span>
            </button>
        @endif

        {{-- Dark mode toggle --}}
        <button class="header-icon-btn" id="darkModeToggle" title="Toggle dark mode" onclick="edmsDarkModeToggle()">
            <i class="fas fa-moon" id="darkModeIcon" style="font-size:1rem"></i>
        </button>

        {{-- Config dropdown --}}
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button class="header-icon-btn" @click="open = !open" title="Configuration">
                <i class="fas fa-cog" style="font-size:1rem"></i>
            </button>
            <div class="edms-dropdown" x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 style="min-width:180px">
                <a class="edms-dropdown-item" href="{{ route('documents.index') }}">
                    <i class="fas fa-file-alt"></i> Documents
                </a>
                <a class="edms-dropdown-item" href="{{ route('workspaces.create') }}">
                    <i class="fas fa-layer-group"></i> Workspaces
                </a>
                <a class="edms-dropdown-item" href="{{ route('tags.index') }}">
                    <i class="fas fa-tags"></i> Tags
                </a>
                <div class="edms-dropdown-divider"></div>
                <a class="edms-dropdown-item" href="{{ route('home') }}">
                    <i class="fas fa-chart-bar"></i> Dashboard
                </a>
            </div>
        </div>

        {{-- Notification Bell (Livewire) --}}
        <livewire:notification-bell />

        {{-- User Menu --}}
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button class="header-user" @click="open = !open">
                <div>
                    <div class="user-name">{{ Auth::user()?->name }}</div>
                    <div class="user-role">
                        @if(Auth::user()?->getRoleNames()->isNotEmpty())
                            {{ Auth::user()->getRoleNames()->first() }}
                        @else
                            Member
                        @endif
                    </div>
                </div>
                <div class="avatar">
                    {{ strtoupper(substr(Auth::user()?->name ?? 'U', 0, 1)) }}{{ strtoupper(substr(explode(' ', Auth::user()?->name ?? 'U ')[1] ?? '', 0, 1)) }}
                </div>
            </button>

            <div class="edms-dropdown" x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 style="right:0;min-width:200px">

                {{-- User info header --}}
                <div style="padding:0.9rem 1rem;border-bottom:1px solid #f3f4f6">
                    <div style="font-size:0.875rem;font-weight:700;color:#1e293b">{{ Auth::user()?->name }}</div>
                    <div style="font-size:0.75rem;color:#64748b;margin-top:0.1rem">{{ Auth::user()?->email }}</div>
                    @if(Auth::user()?->getRoleNames()->isNotEmpty())
                        <span class="role-badge role-{{ Auth::user()->getRoleNames()->first() }}" style="margin-top:0.4rem">
                            {{ Auth::user()->getRoleNames()->first() }}
                        </span>
                    @endif
                </div>

                <a class="edms-dropdown-item" href="{{ route('workspaces.create') }}">
                    <i class="fas fa-user-circle"></i> Profile
                </a>
                <a class="edms-dropdown-item" href="{{ route('home') }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <div class="edms-dropdown-divider"></div>
                <a class="edms-dropdown-item"
                   href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit()"
                   style="color:#dc2626">
                    <i class="fas fa-sign-out-alt" style="color:#dc2626"></i> Sign out
                </a>
            </div>
        </div>
    </div>

    {{-- Logout form --}}
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>

</header>
