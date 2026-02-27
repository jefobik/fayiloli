<div class="flex flex-col gap-1.5 p-2 w-full">
    <p class="text-[0.62rem] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-1 select-none">
        Appearance</p>
    <div class="grid grid-cols-3 gap-1 p-1 bg-slate-100 dark:bg-slate-800 rounded-lg w-full">

        {{-- Light --}}
        <button type="button" wire:click="updateTheme('light')"
            class="flex flex-col items-center gap-1 px-2 py-2.5 rounded-md text-[0.7rem] font-semibold
                   transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-indigo-500
                   {{ $theme === 'light'
                       ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm pointer-events-none'
                       : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}"
            aria-pressed="{{ $theme === 'light' ? 'true' : 'false' }}">
            <i class="fas fa-sun text-base transition-transform duration-150
                      {{ $theme === 'light' ? 'scale-110' : '' }}" aria-hidden="true"></i>
            Light
        </button>

        {{-- Auto (system) --}}
        <button type="button" wire:click="updateTheme('system')"
            class="flex flex-col items-center gap-1 px-2 py-2.5 rounded-md text-[0.7rem] font-semibold
                   transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-indigo-500
                   {{ $theme === 'system'
                       ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm pointer-events-none'
                       : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}"
            aria-pressed="{{ $theme === 'system' ? 'true' : 'false' }}">
            <i class="fas fa-desktop text-base transition-transform duration-150
                      {{ $theme === 'system' ? 'scale-110' : '' }}" aria-hidden="true"></i>
            Auto
        </button>

        {{-- Dark --}}
        <button type="button" wire:click="updateTheme('dark')"
            class="flex flex-col items-center gap-1 px-2 py-2.5 rounded-md text-[0.7rem] font-semibold
                   transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-indigo-500
                   {{ $theme === 'dark'
                       ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm pointer-events-none'
                       : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}"
            aria-pressed="{{ $theme === 'dark' ? 'true' : 'false' }}">
            <i class="fas fa-moon text-base transition-transform duration-150
                      {{ $theme === 'dark' ? '-rotate-12 scale-110' : '' }}" aria-hidden="true"></i>
            Dark
        </button>

    </div>

    @script
    <script>
        $wire.on('theme-updated', (data) => {
            const theme = data.theme;
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = theme === 'dark' || (theme === 'system' && prefersDark);

            // For app.blade.php (Tailwind)
            document.documentElement.classList.toggle('dark', isDark);
            document.documentElement.classList.toggle('dark-mode', isDark);
            document.body.classList.toggle('dark-mode', isDark);
            localStorage.setItem('darkMode', isDark);

            // For central.blade.php (Bootstrap 5)
            if (theme === 'system') {
                document.documentElement.setAttribute('data-bs-theme', prefersDark ? 'dark' : 'light');
            } else {
                document.documentElement.setAttribute('data-bs-theme', theme);
            }
        });
    </script>
    @endscript
</div>