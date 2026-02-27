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
                    <x-ts-badge color="slate" light icon="shield-check" position="left"
                        text="{{ ucfirst($userRoles[0] ?? 'Member') }}" />
                    @if ($tenant)
                        <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                        <x-ts-badge color="indigo" light icon="building-office" position="left"
                            text="{{ $tenant->organization_name }}" />
                    @endif
                </div>
            </div>

            {{-- ── Quick actions (role-gated) ──────────────────────────── --}}
            <div class="flex items-center flex-wrap gap-3">

                @if ($canDocs)
                    <x-ts-button href="{{ route('documents.index') }}" color="indigo" icon="document-text" position="left">
                        Documents
                    </x-ts-button>
                @endif

                {{-- Upload dropdown — Alpine-powered, calls existing JS helpers --}}
                @if ($canUpload)
                    <x-ts-dropdown text="Upload" icon="arrow-up-tray" position="bottom-end">
                        <x-ts-dropdown.items text="Upload Files" icon="document-plus" x-on:click="uploadFiles()" />
                        <x-ts-dropdown.items text="Upload Folder" icon="folder-open" x-on:click="uploadFolder()" />
                        <x-ts-dropdown.items text="Add URL" icon="link" x-on:click="addUrlModal()" />
                    </x-ts-dropdown>
                @endif

                @if ($canTags)
                    <x-ts-button href="{{ route('tags.index') }}" color="white" icon="tag" position="left">
                        Tags
                    </x-ts-button>
                @endif

            </div>
        </div>

        {{-- ── Command Bar (Stripe-style quick-action strip) ─────────────────── --}}
        <div class="flex items-center gap-2 flex-wrap px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">

            @if ($canDocs)
                <a href="{{ route('documents.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors border border-transparent hover:border-indigo-200 dark:hover:border-indigo-800">
                    <i class="fas fa-file-alt text-indigo-500 text-xs" aria-hidden="true"></i>
                    Documents
                </a>
            @endif

            @if ($canUpload)
                <div x-data="{ uploadOpen: false }" class="relative">
                    <button type="button"
                            @click="uploadOpen = !uploadOpen" @click.outside="uploadOpen = false"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                        <i class="fas fa-arrow-up-from-bracket text-slate-400 text-xs" aria-hidden="true"></i>
                        Upload
                        <i class="fas fa-chevron-down text-[0.55rem] text-slate-400 ml-0.5" aria-hidden="true"></i>
                    </button>
                    <div x-show="uploadOpen" x-cloak x-transition
                         class="absolute top-full mt-1 left-0 z-50 w-44 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg overflow-hidden py-1">
                        <button type="button" @click="uploadOpen=false; uploadFiles()"
                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-left">
                            <i class="fas fa-file-arrow-up text-indigo-400 w-4 text-center text-xs" aria-hidden="true"></i> Upload Files
                        </button>
                        <button type="button" @click="uploadOpen=false; uploadFolder()"
                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-left">
                            <i class="fas fa-folder-open text-amber-400 w-4 text-center text-xs" aria-hidden="true"></i> Upload Folder
                        </button>
                        <button type="button" @click="uploadOpen=false; addUrlModal()"
                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-left">
                            <i class="fas fa-link text-sky-400 w-4 text-center text-xs" aria-hidden="true"></i> Add URL
                        </button>
                    </div>
                </div>
            @endif

            @if ($canTags)
                <a href="{{ route('tags.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-violet-50 dark:hover:bg-violet-900/30 hover:text-violet-700 dark:hover:text-violet-300 transition-colors border border-transparent hover:border-violet-200 dark:hover:border-violet-800">
                    <i class="fas fa-tags text-violet-500 text-xs" aria-hidden="true"></i>
                    Tags
                </a>
            @endif

            <div class="flex-1"></div>

            {{-- Keyboard shortcut hint --}}
            <div class="hidden sm:flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium">
                <span>Quick search</span>
                <kbd class="kbd-shortcut">⌘K</kbd>
            </div>
        </div>

        {{-- ── Core Dashboard ────────────────────────────────────────────────── --}}
        {{-- Encloses the Livewire stats engine, providing slots for Blade-only UI components --}}
        <livewire:dashboard-stats>

            <x-slot:sidebar_modules>
                <x-ts-card class="border-slate-200 dark:border-slate-700 shadow-sm">
                    <x-slot:header>
                        <div class="flex items-center justify-between w-full">
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wide">
                                Quick Navigation
                            </h3>
                            @if ($moduleCards->isNotEmpty())
                                <span class="inline-flex items-center justify-center px-2 py-0.5 text-[0.62rem] font-bold
                                             text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900/30 rounded-full">
                                    {{ $moduleCards->count() }}
                                </span>
                            @endif
                        </div>
                    </x-slot:header>

                    @if ($moduleCards->isNotEmpty())
                        <nav aria-label="Module navigation" class="-mx-1">
                            @foreach ($moduleCards as $card)
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
                                   class="flex items-center gap-3 px-2 py-2.5 rounded-lg
                                          hover:bg-slate-50 dark:hover:bg-slate-800/60
                                          transition-colors group">
                                    <div class="w-8 h-8 shrink-0 flex items-center justify-center
                                                rounded-lg {{ $card['bg'] }} {{ $card['color'] }}">
                                        <i class="fa-solid fa-{{ $mod->icon() }} text-sm" aria-hidden="true"></i>
                                    </div>
                                    <span class="flex-1 text-sm font-semibold text-slate-800 dark:text-slate-200
                                                 group-hover:text-indigo-600 dark:group-hover:text-indigo-400
                                                 transition-colors truncate">
                                        {{ $mod->label() }}
                                    </span>
                                    <i class="fas fa-chevron-right text-[0.6rem] text-slate-300 dark:text-slate-600
                                               group-hover:text-indigo-400 transition-colors shrink-0"
                                       aria-hidden="true"></i>
                                </a>
                            @endforeach
                        </nav>
                    @else
                        <div class="flex flex-col items-center justify-center py-8 text-center">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full
                                        bg-slate-100 dark:bg-slate-700 mb-3">
                                <i class="fas fa-lock text-slate-400 dark:text-slate-500" aria-hidden="true"></i>
                            </div>
                            <p class="text-sm font-bold text-slate-900 dark:text-white mb-1">No Modules Enabled</p>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                                Contact your administrator for access.
                            </p>
                        </div>
                    @endif
                </x-ts-card>
            </x-slot:sidebar_modules>

            <x-slot:sidebar_hints>
                @if ($isAdmin)
                    <x-ts-alert color="indigo" light class="mt-6 shadow-sm">
                        <div class="flex items-start xl:items-center gap-3">
                            <i class="fas fa-user-shield mt-0.5 xl:mt-0 w-5 text-center" aria-hidden="true"></i>
                            <p class="text-xs leading-relaxed flex-1">
                                You have <strong class="font-bold">Administrator</strong> access — manage users, roles, and
                                settings.
                            </p>
                            @if ($canUsers)
                                <x-ts-button href="{{ route('users.index') }}" color="indigo" sm outline>
                                    Manage Users
                                </x-ts-button>
                            @endif
                        </div>
                    </x-ts-alert>
                @elseif ($isManager)
                    <x-ts-alert color="sky" light class="mt-6 shadow-sm">
                        <div class="flex items-start sm:items-center gap-3">
                            <i class="fas fa-user-tie mt-0.5 sm:mt-0 text-base" aria-hidden="true"></i>
                            <p class="text-xs leading-relaxed flex-1">
                                You have <strong class="font-bold">Manager</strong> access to view users and the audit log.
                            </p>
                        </div>
                    </x-ts-alert>
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