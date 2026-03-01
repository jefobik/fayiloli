<div class="flex h-screen bg-[var(--app-bg)] overflow-hidden" x-data="{ 
        mobileMenuOpen: false,
        sidebarHovered: false 
     }">

    {{-- --- MOBILE SIDEBAR --- --}}
    <div x-show="mobileMenuOpen" class="relative z-50 lg:hidden" role="dialog" aria-modal="true">
        <div x-show="mobileMenuOpen" x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm"
            @click="mobileMenuOpen = false"></div>

        <div class="fixed inset-0 flex">
            <div x-show="mobileMenuOpen" x-transition:enter="transition ease-in-out duration-300 transform"
                x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in-out duration-300 transform"
                x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                class="relative mr-16 flex w-full max-w-xs flex-1">

                <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                    <button type="button" class="-m-2.5 p-2.5" @click="mobileMenuOpen = false">
                        <span class="sr-only">Close sidebar</span>
                        <x-ts-icon name="x-mark" class="text-white h-6 w-6" />
                    </button>
                </div>

                <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-[var(--panel-bg)] px-6 pb-4 shadow-xl">
                    @include('layouts.sidebar')
                </div>
            </div>
        </div>
    </div>

    {{-- --- DESKTOP SIDEBAR --- --}}
    <aside @mouseenter="sidebarHovered = true" @mouseleave="sidebarHovered = false"
        class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:flex-col border-r border-[var(--panel-border)] bg-[var(--panel-bg)] transition-all duration-300 ease-in-out"
        :class="($wire.sidebarCollapsed && !sidebarHovered) ? 'lg:w-20' : 'lg:w-64'">

        <div class="flex grow flex-col gap-y-5 overflow-y-auto pb-4 px-6">
            @include('layouts.sidebar')
        </div>
    </aside>

    {{-- --- MAIN CONTENT AREA --- --}}
    <div class="flex flex-1 flex-col transition-all duration-300 ease-in-out"
        :class="$wire.sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'">

        {{-- Header --}}
        @include('layouts.header')

        {{-- ── Main Content Area ──────────────────────────────── --}}
        <main class="flex-1 flex flex-col min-w-0 bg-[var(--app-bg)] relative z-0">
            <div class="flex-1 overflow-y-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="max-w-7xl mx-auto">
                    {{ $slot }}
                </div>
            </div>
        </main>
        {{-- Global Notifications --}}
        <x-ts-toast />
        <x-ts-dialog />
    </div>