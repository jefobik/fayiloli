<div class="flex items-center rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm"
    role="group" aria-label="View mode">
    <button type="button" wire:click="setViewMode('grid')"
        class="w-8 h-8 flex items-center justify-center transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[var(--color-primary)] {{ $viewMode === 'grid' ? 'bg-[var(--color-primary)]/10 text-[var(--color-primary)]' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 bg-white dark:bg-slate-800' }}"
        aria-label="Grid view" :aria-pressed="viewMode === 'grid'">
        <i class="fas fa-th-large text-[0.7rem]" aria-hidden="true"></i>
    </button>
    <button type="button" wire:click="setViewMode('list')"
        class="w-8 h-8 flex items-center justify-center border-l border-slate-200 dark:border-slate-700 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[var(--color-primary)] {{ $viewMode === 'list' ? 'bg-[var(--color-primary)]/10 text-[var(--color-primary)]' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 bg-white dark:bg-slate-800' }}"
        aria-label="List view" :aria-pressed="viewMode === 'list'">
        <i class="fas fa-list text-[0.7rem]" aria-hidden="true"></i>
    </button>
</div>