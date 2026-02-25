@extends('layouts.app')

@php
    use App\Enums\TenantModule;

    $tenant = tenancy()->tenant ?? null;
    $authUser = Auth::user();

    // ── Module launchpad: two-layer gate (tenant enabled + Spatie permission) ──
    $moduleCards = collect([
        ['module' => TenantModule::DOCUMENTS, 'permission' => 'view documents', 'color' => 'text-indigo-600 dark:text-indigo-400', 'bg' => 'bg-indigo-100 dark:bg-indigo-900/30', 'border' => 'border-indigo-200 dark:border-indigo-800'],
        ['module' => TenantModule::FOLDERS, 'permission' => 'view folders', 'color' => 'text-sky-600 dark:text-sky-400', 'bg' => 'bg-sky-100 dark:bg-sky-900/30', 'border' => 'border-sky-200 dark:border-sky-800'],
        ['module' => TenantModule::TAGS, 'permission' => 'view tags', 'color' => 'text-violet-600 dark:text-violet-400', 'bg' => 'bg-violet-100 dark:bg-violet-900/30', 'border' => 'border-violet-200 dark:border-violet-800'],
        ['module' => TenantModule::USERS, 'permission' => 'view users', 'color' => 'text-blue-600 dark:text-blue-400', 'bg' => 'bg-blue-100 dark:bg-blue-900/30', 'border' => 'border-blue-200 dark:border-blue-800'],
        ['module' => TenantModule::FILE_REQUESTS, 'permission' => 'view documents', 'color' => 'text-amber-600 dark:text-amber-400', 'bg' => 'bg-amber-100 dark:bg-amber-900/30', 'border' => 'border-amber-200 dark:border-amber-800'],
        ['module' => TenantModule::SHARES, 'permission' => 'share documents', 'color' => 'text-emerald-600 dark:text-emerald-400', 'bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'border' => 'border-emerald-200 dark:border-emerald-800'],
        ['module' => TenantModule::NOTIFICATIONS, 'permission' => 'view notifications', 'color' => 'text-rose-600 dark:text-rose-400', 'bg' => 'bg-rose-100 dark:bg-rose-900/30', 'border' => 'border-rose-200 dark:border-rose-800'],
        ['module' => TenantModule::PROJECTS, 'permission' => null, 'color' => 'text-cyan-600 dark:text-cyan-400', 'bg' => 'bg-cyan-100 dark:bg-cyan-900/30', 'border' => 'border-cyan-200 dark:border-cyan-800'],
        ['module' => TenantModule::HRM, 'permission' => null, 'color' => 'text-green-600 dark:text-green-400', 'bg' => 'bg-green-100 dark:bg-green-900/30', 'border' => 'border-green-200 dark:border-green-800'],
        ['module' => TenantModule::STATS, 'permission' => null, 'color' => 'text-fuchsia-600 dark:text-fuchsia-400', 'bg' => 'bg-fuchsia-100 dark:bg-fuchsia-900/30', 'border' => 'border-fuchsia-200 dark:border-fuchsia-800'],
    ])->filter(function (array $card) use ($tenant, $authUser): bool {
        $enabled = $tenant?->hasModule($card['module']) ?? false;
        $allowed = $card['permission'] ? ($authUser?->can($card['permission']) ?? false) : true;
        return $enabled && $allowed;
    });

    $userRoles = $authUser?->getRoleNames()->toArray() ?? [];
    $isAdmin = in_array('admin', $userRoles);
    $isManager = in_array('manager', $userRoles);

    $canUpload = $authUser?->can('create documents') ?? false;
    $canDocs = ($tenant?->hasModule(TenantModule::DOCUMENTS) ?? false)
        && ($authUser?->can('view documents') ?? false);
    $canTags = ($tenant?->hasModule(TenantModule::TAGS) ?? false)
        && ($authUser?->can('view tags') ?? false);
    $canUsers = ($tenant?->hasModule(TenantModule::USERS) ?? false)
        && ($authUser?->can('view users') ?? false);
@endphp

@section('content')

    <div class="p-4 sm:p-6 lg:p-8 max-w-[100rem] mx-auto space-y-8" x-data="{
                        /*
                         * Time-of-day greeting — computed once on mount and never re-evaluated,
                         * so it does not cause reactive churn on the page.
                         */
                        greeting: (function() {
                            const h = new Date().getHours();
                            return h < 12 ? 'Good morning'
                                 : h < 17 ? 'Good afternoon'
                                 :           'Good evening';
                        })()
                    }">

        {{-- ── Page header ─────────────────────────────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                {{--
                Heading uses x-text so Alpine replaces "Welcome back" with the
                time-aware greeting after JS loads. The hard-coded text is the
                no-JS / pre-hydration fallback — identical appearance, just a
                different salutation.
                --}}
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight"
                    x-text="greeting + ', {{ addslashes($authUser?->name ?? 'User') }}'">
                    Welcome back, {{ $authUser?->name ?? 'User' }}
                </h1>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-slate-500 dark:text-slate-400 font-medium">
                    <span>{{ now()->format('l, F j, Y') }}</span>
                    <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                    <span
                        class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs shadow-sm shadow-slate-200/50 dark:shadow-none border border-slate-200/50 dark:border-slate-700">
                        <i class="fas fa-shield-alt text-[0.6rem]"></i>
                        {{ ucfirst($userRoles[0] ?? 'Member') }}
                    </span>
                    @if ($tenant)
                        <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                        <span
                            class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 text-xs shadow-sm shadow-indigo-100 dark:shadow-none border border-indigo-100 dark:border-indigo-800">
                            <i class="fas fa-building text-[0.65rem]"></i>
                            {{ $tenant->organization_name }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- ── Quick actions (role-gated) ──────────────────────────── --}}
            <div class="flex items-center flex-wrap gap-3">

                @if ($canDocs)
                    <a href="{{ route('documents.index') }}"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm shadow-indigo-200 dark:shadow-none transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                        <i class="fas fa-file-alt"></i>
                        <span>Documents</span>
                    </a>
                @endif

                {{-- Upload dropdown — Alpine-powered, calls existing JS helpers --}}
                @if ($canUpload)
                    <div class="relative z-20" x-data="{ open: false }" @click.outside="open = false"
                        @close.stop="open = false">
                        <button type="button"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 text-sm font-semibold rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                            @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="true"
                            aria-controls="uploadMenu">
                            <i class="fas fa-upload text-indigo-500 dark:text-indigo-400"></i>
                            <span>Upload</span>
                            <i class="fas fa-chevron-down text-[0.62rem] text-slate-400 transition-transform duration-200"
                                :class="open ? 'rotate-180' : ''" aria-hidden="true"></i>
                        </button>

                        <div id="uploadMenu"
                            class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg ring-1 ring-black ring-opacity-5 divide-y divide-slate-100 dark:divide-slate-700 overflow-hidden transform origin-top-right transition-all"
                            x-show="open" x-cloak x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 -translate-y-2" role="menu" aria-orientation="vertical"
                            aria-label="Upload options">
                            <div class="py-1" role="none">
                                <button type="button" role="menuitem"
                                    class="group flex items-center w-full px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors focus:outline-none"
                                    @click="uploadFiles(); open = false">
                                    <i class="fas fa-file-lines w-5 text-center text-indigo-500 dark:text-indigo-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 mr-2"
                                        aria-hidden="true"></i>
                                    Upload Files
                                </button>
                                <button type="button" role="menuitem"
                                    class="group flex items-center w-full px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors focus:outline-none"
                                    @click="uploadFolder(); open = false">
                                    <i class="fas fa-folder-open w-5 text-center text-indigo-500 dark:text-indigo-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 mr-2"
                                        aria-hidden="true"></i>
                                    Upload Folder
                                </button>
                                <button type="button" role="menuitem"
                                    class="group flex items-center w-full px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors focus:outline-none"
                                    @click="addUrlModal(); open = false">
                                    <i class="fas fa-link w-5 text-center text-indigo-500 dark:text-indigo-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 mr-2"
                                        aria-hidden="true"></i>
                                    Add URL
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($canTags)
                    <a href="{{ route('tags.index') }}"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 text-sm font-semibold rounded-lg shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                        <i class="fas fa-tags text-indigo-500 dark:text-indigo-400"></i>
                        <span>Tags</span>
                    </a>
                @endif

            </div>
        </div>

        {{-- ── Core Dashboard ────────────────────────────────────────────────── --}}
        {{-- Encloses the Livewire stats engine, providing slots for Blade-only UI components --}}
        <livewire:dashboard-stats>

            <x-slot:sidebar_modules>
                @if ($moduleCards->isNotEmpty())
                    <div class="mt-8 xl:mt-0 flex items-center gap-3 mb-4">
                        <h2 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                            Workspace Modules
                        </h2>
                        <span
                            class="inline-flex items-center justify-center px-2 py-0.5 text-[0.65rem] font-bold leading-none text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900/30 rounded-full">
                            {{ $moduleCards->count() }}
                        </span>
                        <div class="flex-grow border-t border-slate-200 dark:border-slate-700"></div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-4" role="list"
                        aria-label="Available workspace modules">
                        @foreach ($moduleCards as $card)
                            @php
                                $mod = $card['module'];
                                try {
                                    $landingUrl = route($mod->landingRoute());
                                } catch (\Exception) {
                                    $landingUrl = '#';
                                }
                            @endphp

                            <div class="group relative bg-white dark:bg-slate-800 flex items-center p-4 overflow-hidden rounded-xl shadow-sm hover:shadow-md border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-600 transition-all duration-300"
                                role="listitem">

                                <div
                                    class="absolute -right-8 -top-8 w-24 h-24 bg-gradient-to-br from-indigo-100 to-white dark:from-indigo-900/30 dark:to-slate-800 rounded-full blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none">
                                </div>

                                <div class="relative z-10 flex items-center gap-4 w-full">
                                    <div
                                        class="flex items-center justify-center w-10 h-10 shrink-0 rounded-lg {{ $card['bg'] }} {{ $card['color'] }} border {{ $card['border'] }} shadow-sm transition-transform duration-300 group-hover:scale-105 group-hover:ring-2 group-hover:ring-{{ explode('-', $card['color'])[1] }}-50 dark:group-hover:ring-{{ explode('-', $card['color'])[1] }}-900/20">
                                        <i class="fa-solid fa-{{ $mod->icon() }} text-lg" aria-hidden="true"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3
                                            class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate">
                                            {{ $mod->label() }}
                                        </h3>
                                        <p class="text-[0.7rem] text-slate-500 dark:text-slate-400 line-clamp-1 font-medium mt-0.5">
                                            {{ $mod->description() }}
                                        </p>
                                    </div>
                                    @if ($landingUrl !== '#')
                                        <a href="{{ $landingUrl }}"
                                            class="absolute inset-0 z-20 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-xl"
                                            aria-label="Open {{ $mod->label() }}"></a>
                                        <i
                                            class="fas fa-chevron-right text-[0.7rem] text-slate-300 dark:text-slate-600 group-hover:text-indigo-500 group-hover:translate-x-0.5 transition-all z-10 relative"></i>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div
                        class="mt-8 xl:mt-0 py-10 px-6 text-center bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 border-dashed rounded-xl flex flex-col items-center justify-center">
                        <div
                            class="flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-700 mb-3 ring-4 ring-slate-50 dark:ring-slate-800">
                            <i class="fas fa-lock text-xl text-slate-400 dark:text-slate-500" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-1">No Modules Enabled</h3>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 max-w-xs">
                            Contact your administrator for access.
                        </p>
                    </div>
                @endif
            </x-slot:sidebar_modules>

            <x-slot:sidebar_hints>
                @if ($isAdmin)
                    <div class="mt-6 flex flex-col sm:flex-row xl:flex-col items-start xl:items-stretch sm:items-center gap-4 p-4 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/60 shadow-sm"
                        role="note" aria-label="Administrator notice">
                        <div class="flex items-start xl:items-center gap-3 text-indigo-800 dark:text-indigo-300">
                            <i class="fas fa-user-shield text-indigo-600 dark:text-indigo-400 mt-0.5 xl:mt-0 w-5 text-center"
                                aria-hidden="true"></i>
                            <p class="text-xs leading-relaxed">
                                You have <strong class="font-bold">Administrator</strong> access — manage users, roles, and
                                settings.
                            </p>
                        </div>
                        @if ($canUsers)
                            <a href="{{ route('users.index') }}"
                                class="sm:ml-auto xl:ml-0 inline-flex items-center justify-center px-4 py-1.5 text-[0.65rem] font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-300 bg-white dark:bg-slate-800 border border-indigo-200 dark:border-indigo-700 rounded-lg hover:bg-indigo-100 dark:hover:bg-slate-700 hover:text-indigo-800 dark:hover:text-indigo-200 transition-colors focus:ring-2 focus:ring-indigo-500 focus:outline-none whitespace-nowrap">
                                Manage Users
                            </a>
                        @endif
                    </div>
                @elseif ($isManager)
                    <div class="mt-6 flex items-start sm:items-center gap-3 p-4 rounded-xl bg-sky-50 dark:bg-sky-900/20 border border-sky-100 dark:border-sky-800/60 shadow-sm"
                        role="note" aria-label="Manager notice">
                        <i class="fas fa-user-tie text-sky-600 dark:text-sky-400 mt-0.5 sm:mt-0 text-base"
                            aria-hidden="true"></i>
                        <p class="text-xs text-sky-800 dark:text-sky-300 leading-relaxed">
                            You have <strong class="font-bold">Manager</strong> access to view users and the audit log.
                        </p>
                    </div>
                @endif
            </x-slot:sidebar_hints>

        </livewire:dashboard-stats>

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

@endsection