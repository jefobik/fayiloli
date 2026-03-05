<div class="folder-tree-container flex flex-col h-full">
    {{-- New Workspace Trigger --}}
    <div class="px-2 mb-2">
        <button type="button" wire:click="$set('showCreateModal', true)"
            class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 text-xs font-bold hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-all border border-indigo-100 dark:border-indigo-900/30">
            <i class="fas fa-plus-circle text-[0.7rem]"></i>
            <span>New Workspace</span>
        </button>
    </div>

    <ul class="flex-1 overflow-y-auto space-y-0.5 scrollbar-none" aria-label="Workspace folders">
        @foreach ($folders as $folder)
            <li class="folder-item" id="folder-{{ $folder->id }}" wire:key="folder-{{ $folder->id }}">
                <div class="flex items-center group">
                    {{-- Toggle button if has subfolders --}}
                    @if ($folder->subfolders->isNotEmpty())
                        <button type="button" wire:click.stop="toggleFolder('{{ $folder->id }}')"
                            class="w-6 h-6 flex items-center justify-center hover:bg-slate-100 dark:hover:bg-slate-800 rounded-md transition-colors text-slate-400 focus:outline-none"
                            aria-label="{{ in_array($folder->id, $expandedFolders) ? 'Collapse' : 'Expand' }}">
                            <i
                                class="fas {{ in_array($folder->id, $expandedFolders) ? 'fa-chevron-down' : 'fa-chevron-right' }} text-[0.6rem] transition-transform duration-200"></i>
                        </button>
                    @else
                        <div class="w-6 h-6"></div>
                    @endif

                    {{-- Folder name / Select --}}
                    <a href="javascript:void(0)" wire:click="selectFolder('{{ $folder->id }}')"
                        class="flex-1 flex items-center gap-2 px-2 py-1.5 rounded-md text-sm font-semibold transition-all duration-200 truncate
                                      {{ $activeFolderId == $folder->id
            ? 'bg-[var(--color-primary)]/10 text-[var(--color-primary)] dark:bg-[var(--color-primary)]/20 shadow-sm border-l-[3px] border-[var(--color-primary)] pl-1.5'
            : 'text-[var(--color-text-main)] hover:bg-[var(--color-surface-hover)] dark:text-[var(--color-text-main-dark)] border-l-[3px] border-transparent' }}">

                        <i class="fas fa-folder {{ $activeFolderId == $folder->id ? 'text-[var(--color-primary)]' : 'text-slate-400 group-hover:text-[var(--color-primary)]' }} shrink-0 w-5 text-center transition-colors"
                            aria-hidden="true"></i>

                        <span x-show="!sidebarCollapsed || sidebarHovered" class="truncate font-bold tracking-tight">
                            {{ $folder->name }}
                        </span>
                    </a>
                </div>

                {{-- Recursion for Subfolders --}}
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
    </ul>

    {{-- Create Folder Modal --}}
    <x-ts-modal wire:model="showCreateModal" title="Create New Workspace" size="sm" persistent>
        <div class="space-y-4">
            <x-ts-input wire:model="newFolderName" label="Workspace Name" placeholder="E.g. Marketing Docs"
                hint="Give your workspace a clear, descriptive name." />

            <x-ts-select.styled wire:model="newFolderParentId" label="Parent Workspace (Optional)"
                placeholder="Top-level Workspace" :options="$allFolders" select="label:name|value:id" searchable />
        </div>
        <x-slot:footer>
            <div class="flex justify-end gap-2">
                <x-ts-button color="slate" variant="ghost" x-on:click="show = false">Cancel</x-ts-button>
                <x-ts-button color="indigo" wire:click="createFolder" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="createFolder">Create Workspace</span>
                    <span wire:loading wire:target="createFolder">Creating...</span>
                </x-ts-button>
            </div>
        </x-slot:footer>
    </x-ts-modal>
</div>