<header
    class="sticky top-0 z-40 flex w-full h-16 shrink-0 items-center justify-between gap-x-4 border-b border-slate-200 bg-white/90 backdrop-blur-md px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8 dark:bg-slate-900/90 dark:border-slate-800 transition-colors"
    role="banner">

    <div class="flex items-center gap-4">
        {{-- ── Sidebar Toggle ─────────────────────────── --}}
        <button type="button"
            class="-m-2.5 p-2.5 text-slate-700 dark:text-slate-300 lg:hidden hover:bg-slate-100 dark:hover:bg-slate-800 rounded-md transition-colors"
            @click="sidebarOpen = !sidebarOpen" :aria-expanded="sidebarOpen.toString()"
            aria-controls="renderSidebarHtmlId" aria-label="Toggle sidebar navigation">
            <span class="sr-only">Open sidebar</span>
            <svg aria-hidden="true" focusable="false" fill="none" class="h-6 w-6" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10" />
            </svg>
        </button>

        {{-- ── Brand (shown on mobile when sidebar closed) ──────── --}}
        <div class="lg:hidden flex items-center" aria-hidden="true">
            <a href="{{ route('home') }}" tabindex="-1" wire:navigate
                class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400 no-underline">
                <img src="/img/fayiloli-icon.svg" alt="" aria-hidden="true"
                    class="w-8 h-8 shrink-0 rounded-lg shadow-sm">
                <span class="font-bold text-lg tracking-tight text-slate-900 dark:text-white">Ostrich</span>
            </a>
        </div>
    </div>

    {{-- ── Global Search (MeiliSearch-powered Livewire) ──────── --}}
    @if (!Route::is('home'))
        <div class="flex items-center gap-2">
            <livewire:global-search />
            <kbd class="kbd-shortcut hidden lg:inline-flex" title="Keyboard shortcut: Ctrl+K or Cmd+K">⌘K</kbd>
        </div>
    @endif

    {{-- ── Spacer ─────────────────────────────────── --}}
    <div style="flex:1" aria-hidden="true"></div>

    {{-- ── Header Actions ──────────────────────────── --}}
    <div class="flex items-center gap-x-2 sm:gap-x-4 lg:gap-x-6" role="toolbar" aria-label="Header actions">

        {{-- Quick Upload --}}
        @if (Route::is('documents.index'))
            <x-ts-button color="indigo" icon="arrow-up-tray" position="left" onclick="uploadFiles()"
                aria-label="Upload document" class="hidden sm:inline-flex shadow-sm hover:shadow-md transition-all">
                Upload
            </x-ts-button>
            <x-ts-button color="indigo" icon="arrow-up-tray" position="left" onclick="uploadFiles()"
                aria-label="Upload document"
                class="sm:hidden w-10 h-10 !px-0 flex items-center justify-center rounded-full shadow-sm">
            </x-ts-button>
        @endif

        {{-- Separator --}}
        <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-slate-200 dark:lg:bg-slate-700" aria-hidden="true"></div>

        <div class="flex items-center gap-1 sm:gap-2">
            {{-- Dark mode toggle --}}
            <button type="button"
                class="w-10 h-10 flex items-center justify-center rounded-full text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                id="darkModeToggle" aria-label="Toggle dark mode" aria-pressed="false"
                onclick="edmsDarkModeToggle(this)">
                <i class="fas fa-moon" id="darkModeIcon" aria-hidden="true"></i>
            </button>

            {{-- Config dropdown --}}
            <x-ts-dropdown icon="cog" position="bottom-end">
                <x-slot:action>
                    <button type="button"
                        class="w-10 h-10 flex items-center justify-center rounded-full text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                        aria-label="Configuration menu">
                        <i class="fas fa-cog" aria-hidden="true"></i>
                    </button>
                </x-slot:action>
                <x-ts-dropdown.items icon="document-text" text="Documents" href="{{ route('documents.index') }}" />
                <x-ts-dropdown.items icon="folder" text="Workspaces" href="{{ route('folders.index') }}" />
                <x-ts-dropdown.items icon="tag" text="Tags" href="{{ route('tags.index') }}" />
                <x-ts-dropdown.items separator />
                <x-ts-dropdown.items icon="chart-bar" text="Dashboard" href="{{ route('home') }}" />
            </x-ts-dropdown>
        </div>

        {{-- Tenant Switcher --}}
        @php
            $currentTenant = tenancy()->initialized ? tenancy()->tenant : null;
            $portalUrl     = rtrim(config('app.url'), '/') . '/portal';

            // Type → [gradient-from, gradient-to] for workspace chips
            $wsTypeColors = [
                'government'  => ['#dc2626', '#b91c1c'],
                'secretariat' => ['#4f46e5', '#4338ca'],
                'agency'      => ['#0284c7', '#0369a1'],
                'department'  => ['#16a34a', '#15803d'],
                'unit'        => ['#d97706', '#b45309'],
            ];

            $availableTenants = collect();
            $user = auth()->user();
            if ($currentTenant && $user && $user->isAdminOrAbove()) {
                try {
                    // Query central database (Tenant model uses central_connection) for
                    // other active workspaces — powers the header workspace switcher.
                    $availableTenants = \App\Models\Tenant::with('domains')
                        ->where('status', \App\Enums\TenantStatus::ACTIVE)
                        ->where('id', '!=', $currentTenant->id)
                        ->orderBy('organization_name')
                        ->get();
                } catch (\Exception $e) {
                    // Silently degrade — switcher hidden when central DB unreachable.
                }
            }
        @endphp
        @if($currentTenant)
            <div class="hidden sm:block lg:h-6 lg:w-px lg:bg-slate-200 dark:lg:bg-slate-700" aria-hidden="true"></div>

            <x-ts-dropdown position="bottom-end">
                <x-slot:action>
                    @php
                        $planChipColors = [
                            'government'  => ['#dc2626','#b91c1c'],
                            'secretariat' => ['#4f46e5','#4338ca'],
                            'agency'      => ['#0284c7','#0369a1'],
                            'department'  => ['#16a34a','#15803d'],
                            'unit'        => ['#d97706','#b45309'],
                        ];
                        $chipGc = $planChipColors[$currentTenant->tenant_type?->value ?? ''] ?? ['#7c3aed','#6d28d9'];
                        $chipInitials = strtoupper(
                            substr($currentTenant->organization_name, 0, 1) .
                            substr(explode(' ', $currentTenant->organization_name)[1] ?? '', 0, 1)
                        );
                    @endphp
                    <button type="button"
                        class="flex items-center gap-2 pl-1.5 pr-2.5 py-1.5 rounded-lg
                               bg-slate-50 dark:bg-slate-800/70
                               border border-slate-200 dark:border-slate-700
                               hover:border-indigo-300 dark:hover:border-indigo-600
                               transition-all duration-150
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        aria-label="Switch Workspace. Current: {{ $currentTenant->organization_name }}">
                        <div class="w-6 h-6 rounded-md shrink-0 flex items-center justify-center
                                    text-white text-[0.6rem] font-extrabold shadow-sm"
                             style="background: linear-gradient(135deg, {{ $chipGc[0] }}, {{ $chipGc[1] }});"
                             aria-hidden="true">{{ $chipInitials }}</div>
                        <span class="hidden md:block text-xs font-semibold text-slate-700 dark:text-slate-200
                                     max-w-25 truncate leading-tight">
                            {{ $currentTenant->organization_name }}
                        </span>
                        <i class="fas fa-chevron-down text-[0.6rem] text-slate-400 dark:text-slate-500"
                           aria-hidden="true"></i>
                    </button>
                </x-slot:action>

                <div class="px-4 py-3 border-b flex flex-col items-start border-slate-100 dark:border-slate-800">
                    <p
                        class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest leading-none mb-1">
                        Current Workspace</p>
                    <p class="text-sm font-bold text-slate-900 dark:text-white truncate">
                        {{ $currentTenant->organization_name }}
                    </p>
                </div>

                <a class="group flex flex-row items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors no-underline"
                    role="menuitem" href="{{ $portalUrl }}">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 shrink-0 group-hover:scale-105 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/50 transition-all"
                        aria-hidden="true">
                        <i class="fas fa-arrow-right-arrow-left"></i>
                    </div>
                    <div class="flex flex-col items-start justify-center">
                        <div class="text-[0.85rem] font-bold text-indigo-700 dark:text-indigo-300 leading-none">Central
                            Portal</div>
                        <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1 leading-none">Go to
                            management dashboard</div>
                    </div>
                </a>

                @if($availableTenants->isNotEmpty())
                    <div class="px-4 py-2 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-0">
                            Switch Workspace</p>
                    </div>

                    <div class="max-h-64 overflow-y-auto">
                        @foreach($availableTenants as $t)
                            @php
                                // First-letter initials from first two words of org name
                                $wsWords    = array_values(array_filter(explode(' ', $t->organization_name)));
                                $wsInitials = strtoupper(
                                    substr($wsWords[0] ?? '', 0, 1) . substr($wsWords[1] ?? '', 0, 1)
                                );
                                $wsColors   = $wsTypeColors[$t->tenant_type?->value ?? ''] ?? ['#7c3aed', '#6d28d9'];
                                // Cross-workspace SSO route lives on the CURRENT tenant domain —
                                // WorkspaceSwitchController handles the central DB impersonation token.
                                $switchUrl  = route('switch.workspace', $t->id);
                            @endphp
                            <a href="{{ $switchUrl }}"
                                class="group flex flex-row items-center gap-3 px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-800/50 border-b border-slate-50 dark:border-slate-800/30 transition-colors no-underline last:border-0"
                                role="menuitem"
                                aria-label="Switch to {{ $t->organization_name }}">
                                {{-- Coloured avatar matching org type --}}
                                <div class="flex items-center justify-center w-7 h-7 rounded shrink-0
                                            text-white text-[0.6rem] font-extrabold shadow-sm
                                            transition-transform group-hover:scale-105"
                                     style="background: linear-gradient(135deg, {{ $wsColors[0] }}, {{ $wsColors[1] }});"
                                     aria-hidden="true">
                                    {{ $wsInitials }}
                                </div>
                                <div class="flex flex-col items-start justify-center overflow-hidden w-full">
                                    <div class="text-[0.8rem] font-semibold text-slate-700 dark:text-slate-200
                                                group-hover:text-indigo-600 dark:group-hover:text-indigo-400
                                                transition-colors truncate w-full leading-tight">
                                        {{ $t->organization_name }}
                                    </div>
                                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400
                                                truncate w-full mt-0.5 leading-tight">
                                        {{ $t->domains->first()?->domain ?? 'No domain' }}
                                    </div>
                                </div>
                                {{-- Arrow hint on hover --}}
                                <i class="fas fa-arrow-right text-[0.6rem] text-slate-300 dark:text-slate-600
                                          group-hover:text-indigo-400 transition-colors shrink-0"
                                   aria-hidden="true"></i>
                            </a>
                        @endforeach
                    </div>
                @endif
            </x-ts-dropdown>
        @endif

        {{-- Notification Bell (Livewire) --}}
        <div class="hidden sm:block lg:h-6 lg:w-px lg:bg-slate-200 dark:lg:bg-slate-700" aria-hidden="true"></div>
        <livewire:notification-bell />

        {{-- User Menu --}}
        <div class="hidden sm:block lg:h-6 lg:w-px lg:bg-slate-200 dark:lg:bg-slate-700" aria-hidden="true"></div>

        <x-ts-dropdown position="bottom-end">
            <x-slot:action>
                <button type="button"
                    class="flex items-center gap-x-3 p-1 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                    aria-label="User menu for {{ Auth::user()?->name }}">
                    <span class="hidden lg:flex lg:items-center">
                        <span class="text-sm font-bold leading-6 text-slate-900 dark:text-white"
                            aria-hidden="true">{{ Auth::user()?->name }}</span>
                    </span>
                    <div class="flex items-center justify-center w-8 h-8 rounded-full text-[0.7rem] font-bold text-white bg-gradient-to-br from-indigo-600 to-purple-600 shrink-0 shadow-sm"
                        aria-hidden="true">
                        {{ strtoupper(substr(Auth::user()?->name ?? 'U', 0, 1)) }}{{ strtoupper(substr(explode(' ', Auth::user()?->name ?? 'U ')[1] ?? '', 0, 1)) }}
                    </div>
                </button>
            </x-slot:action>

            <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ Auth::user()?->name }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ Auth::user()?->email }}</p>
                @if(Auth::user()?->getRoleNames()->isNotEmpty())
                    <div class="mt-2">
                        <span
                            class="inline-flex items-center px-1.5 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 font-bold tracking-wide uppercase text-[0.55rem] border border-indigo-200 dark:border-indigo-800">
                            {{ Auth::user()->getRoleNames()->first() }}
                        </span>
                    </div>
                @endif
            </div>

            <div class="px-3 py-2 border-b border-slate-100 dark:border-slate-800">
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

    // Cmd+K / Ctrl+K → focus global search input
    document.addEventListener('keydown', function (e) {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            var el = document.querySelector('input[type="search"], [data-search]');
            if (el) { el.focus(); el.select(); }
        }
    });

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