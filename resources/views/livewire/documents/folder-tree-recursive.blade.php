@foreach ($folders as $folder)
    <li class="folder-item" id="folder-{{ $folder->id }}" wire:key="folder-{{ $folder->id }}">
        <div class="flex items-center group">
            @if ($folder->subfolders->isNotEmpty())
                <button type="button" wire:click.stop="toggleFolder('{{ $folder->id }}')"
                    class="w-6 h-6 flex items-center justify-center hover:bg-slate-100 dark:hover:bg-slate-800 rounded-md transition-colors text-slate-400 focus:outline-none">
                    <i
                        class="fas {{ in_array($folder->id, $expandedFolders) ? 'fa-chevron-down' : 'fa-chevron-right' }} text-[0.6rem] transition-transform duration-200"></i>
                </button>
            @else
                <div class="w-6 h-6"></div>
            @endif

            <a href="javascript:void(0)" wire:click="selectFolder('{{ $folder->id }}')"
                class="flex-1 flex items-center gap-2 px-2 py-1.5 rounded-md text-sm font-semibold transition-all duration-200 truncate
                              {{ $activeFolderId == $folder->id
            ? 'bg-[var(--color-primary)]/10 text-[var(--color-primary)] dark:bg-[var(--color-primary)]/20 shadow-sm border-l-[3px] border-[var(--color-primary)] pl-1.5'
            : 'text-[var(--color-text-main)] hover:bg-[var(--color-surface-hover)] dark:text-[var(--color-text-main-dark)] border-l-[3px] border-transparent' }}">
                <i class="fas fa-folder {{ $activeFolderId == $folder->id ? 'text-[var(--color-primary)]' : 'text-slate-400 group-hover:text-[var(--color-primary)]' }} shrink-0 w-5 text-center transition-colors"
                    aria-hidden="true"></i>
                <span class="truncate font-bold tracking-tight">{{ $folder->name }}</span>
            </a>
        </div>

        @if (in_array($folder->id, $expandedFolders) && $folder->subfolders->isNotEmpty())
            <ul class="ml-4 mt-0.5 border-l border-slate-200 dark:border-slate-800 space-y-0.5">
                @include('livewire.documents.folder-tree-recursive', [
                    'folders' => $folder->subfolders,
                    'activeFolderId' => $activeFolderId,
                    'expandedFolders' => $expandedFolders,
                ])
                    </ul>
        @endif
        </li>
@endforeach