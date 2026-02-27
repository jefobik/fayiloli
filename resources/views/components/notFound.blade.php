@props([
    'title' => 'No Results Found',
    'message' => 'There are currently no items matching your criteria in this view.',
    'icon' => 'far fa-folder-open',
    'actionText' => null,
    'actionUrl' => null,
    'actionWire' => null,
])

<div class="col-md-12 mb-4 w-full h-full flex items-center justify-center">
    <div class="flex flex-col items-center justify-center p-12 mt-6 bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 border-dashed rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 w-full max-w-2xl mx-auto text-center relative overflow-hidden group">
        
        {{-- Decorative Background Blob --}}
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/50 to-transparent dark:from-indigo-900/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>

        <div class="relative z-10 flex items-center justify-center w-20 h-20 mb-5 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-400 dark:text-slate-500 ring-8 ring-slate-50 dark:ring-slate-800/50 group-hover:scale-110 group-hover:text-indigo-500 transition-all duration-300">
            <i class="{{ $icon }} text-3xl" aria-hidden="true"></i>
        </div>
        
        <h3 class="relative z-10 text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $title }}</h3>
        <p class="relative z-10 mt-2.5 text-sm font-medium text-slate-500 dark:text-slate-400 max-w-sm mx-auto leading-relaxed">{{ $message }}</p>

        @if ($actionText)
            <div class="mt-8 relative z-10">
                @if ($actionUrl)
                    <x-ts-button href="{{ $actionUrl }}" wire:navigate.hover color="primary" class="shadow-sm shadow-indigo-500/20 px-6 font-bold tracking-wide">
                        {{ $actionText }}
                    </x-ts-button>
                @elseif ($actionWire)
                    <x-ts-button wire:click="{{ $actionWire }}" color="primary" class="shadow-sm shadow-indigo-500/20 px-6 font-bold tracking-wide">
                        {{ $actionText }}
                    </x-ts-button>
                @endif
            </div>
        @endif
    </div>
</div>