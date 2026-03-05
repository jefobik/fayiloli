<div class="edms-browser flex flex-1 min-h-0 flex-col overflow-hidden bg-[var(--color-surface-muted)] dark:bg-[var(--color-surface-muted-dark)] w-full h-full"
    x-data="{ 
         isDragging: @entangle('isDragging'),
         detailVisible: @entangle('selectedDocumentId'),
         sidebarCollapsed: @entangle('sidebarCollapsed').live
     }" @dragover.window.prevent="isDragging = true" @dragleave.window.prevent="isDragging = false"
    @drop.window.prevent="isDragging = false">

    {{-- ── Drop zone overlay ──────────────────────────────────────────────── --}}
    <div x-show="isDragging" x-cloak class="fixed inset-0 z-[200] pointer-events-none flex items-center justify-center">
        <div
            class="absolute inset-0 bg-indigo-600/10 backdrop-blur-sm border-4 border-dashed border-indigo-400 rounded-none">
        </div>
        <div
            class="relative bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-2xl px-10 py-8 shadow-2xl text-center border border-[var(--color-primary)]/20 shadow-indigo-500/20">
            <div
                class="w-16 h-16 bg-indigo-50 dark:bg-indigo-900/30 rounded-full flex items-center justify-center mx-auto mb-4 text-indigo-500">
                <i class="fas fa-cloud-upload-alt text-3xl animate-bounce"></i>
            </div>
            <p class="text-base font-extrabold text-slate-900 dark:text-white">Drop files to upload</p>
            <p class="text-xs text-slate-500 mt-1">Files will be added to the current workspace</p>
        </div>
    </div>

    {{-- ── COMMAND BAR ────────────────────────────────────────────────────── --}}
    <div
        class="flex items-center gap-1.5 sm:gap-2 flex-wrap px-3 sm:px-4 py-2 border-b border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] shrink-0 shadow-sm z-10">

        {{-- Search Bar --}}
        <div class="relative flex-1 max-w-xs group">
            <i
                class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[0.7rem] group-focus-within:text-indigo-500 transition-colors"></i>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search documents..."
                class="w-full pl-9 pr-4 py-1.5 text-xs rounded-lg border border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)] bg-[var(--color-surface-muted)] dark:bg-[var(--color-surface-muted-dark)] text-[var(--color-text-main)] dark:text-[var(--color-text-main-dark)] focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none">
        </div>

        <div class="flex-1 min-w-0"></div>

        <div class="flex items-center gap-2">
            {{-- View Toggle --}}
            <livewire:documents.view-toggle />

            <div class="h-6 w-px bg-[var(--color-border-subtle)] dark:bg-[var(--color-border-subtle-dark)] mx-1"></div>

            {{-- Secondary Action: Request --}}
            <button type="button" wire:click="$set('showRequestModal', true)"
                class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-slate-600 dark:text-slate-300 bg-[var(--color-surface-muted)] dark:bg-[var(--color-surface-muted-dark)] hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg border border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)] transition-all">
                <i class="fas fa-paper-plane text-[0.7rem]"></i>
                <span class="hidden sm:inline">Request Files</span>
            </button>

            {{-- Primary Action: Upload --}}
            <label
                class="cursor-pointer inline-flex items-center gap-2 px-3 py-1.5 text-xs font-bold bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white rounded-lg shadow-sm shadow-indigo-500/20 transition-all">
                <i class="fas fa-plus text-[0.7rem]"></i>
                <span class="hidden sm:inline">Add Files</span>
                <input type="file" wire:model.live="uploads" multiple class="hidden">
            </label>
        </div>
    </div>

    {{-- ── MAIN AREA ──────────────────────────────────────────────────────── --}}
    <div class="flex flex-1 overflow-hidden relative">

        {{-- Document Listings --}}
        <div class="flex-1 overflow-y-auto p-4 scrollbar-thin scrollbar-thumb-slate-200 dark:scrollbar-thumb-slate-800">

            @if($documents->isEmpty())
                <div class="h-full flex flex-col items-center justify-center text-center p-8">
                    <div
                        class="w-16 h-16 bg-slate-50 dark:bg-slate-900 rounded-full flex items-center justify-center mb-4 text-slate-300">
                        <i class="fas fa-folder-open text-3xl"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">No documents found</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-[200px]">Try adjusting your search or upload files to this
                        workspace.</p>
                </div>
            @else
                @if($viewMode === 'grid')
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4">
                        @foreach($documents as $doc)
                            <div wire:key="doc-grid-{{ $doc->id }}" wire:click="selectDocument('{{ $doc->id }}')"
                                class="group relative bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] border border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)] rounded-xl overflow-hidden cursor-pointer transition-all hover:shadow-xl hover:-translate-y-0.5
                                                    {{ $selectedDocumentId == $doc->id ? 'ring-2 ring-indigo-500 shadow-indigo-500/10 bg-indigo-50/30 dark:bg-indigo-900/10' : '' }}">

                                {{-- Thumbnail Area --}}
                                <div
                                    class="aspect-[4/3] bg-[var(--color-surface-muted)] dark:bg-[var(--color-surface-muted-dark)] flex items-center justify-center relative overflow-hidden">
                                    <i
                                        class="fas {{ $this->getIconFor($doc->extension) }} text-4xl group-hover:scale-110 transition-transform duration-300"></i>

                                    {{-- Hover Actions Overlay --}}
                                    <div
                                        class="absolute inset-0 bg-indigo-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                        <button
                                            class="w-8 h-8 rounded-full bg-white text-indigo-600 flex items-center justify-center hover:bg-indigo-50 transition-colors shadow-lg">
                                            <i class="fas fa-eye text-xs"></i>
                                        </button>
                                        <a href="{{ asset($doc->file_path) }}" download
                                            class="w-8 h-8 rounded-full bg-white text-emerald-600 flex items-center justify-center hover:bg-emerald-50 transition-colors shadow-lg">
                                            <i class="fas fa-download text-xs"></i>
                                        </a>
                                    </div>
                                </div>

                                {{-- Meta Area --}}
                                <div class="p-3">
                                    <h4 class="text-[0.7rem] font-bold truncate text-[var(--color-text-main)] dark:text-[var(--color-text-main-dark)] leading-tight mb-1"
                                        title="{{ $doc->name }}">
                                        {{ $doc->name }}
                                    </h4>
                                    <div class="flex items-center justify-between mt-auto">
                                        <p class="text-[0.6rem] text-slate-400 font-medium tracking-tight">
                                            {{ $doc->created_at->format('d M, Y') }}</p>
                                        <span
                                            class="text-[0.55rem] font-bold uppercase px-1.5 py-0.5 rounded {{ $doc->visibility === 'public' ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20' : 'text-amber-600 bg-amber-50 dark:bg-amber-900/20' }}">
                                            {{ $doc->visibility }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- List View --}}
                    <div
                        class="bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] border border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)] rounded-xl overflow-hidden shadow-sm">
                        <table class="w-full text-[0.7rem] text-left border-collapse">
                            <thead>
                                <tr
                                    class="bg-[var(--color-surface-muted)] dark:bg-[var(--color-surface-muted-dark)] text-slate-400 font-bold uppercase tracking-widest border-b border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)]">
                                    <th class="px-4 py-2.5">Document Name</th>
                                    <th class="px-4 py-2.5">Owner</th>
                                    <th class="px-4 py-2.5">Date Added</th>
                                    <th class="px-4 py-2.5">Size</th>
                                    <th class="px-4 py-2.5 text-right w-20">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($documents as $doc)
                                    <tr wire:key="doc-list-{{ $doc->id }}" wire:click="selectDocument('{{ $doc->id }}')"
                                        class="group border-b last:border-0 border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)] hover:bg-[var(--color-surface-hover)] dark:hover:bg-[var(--color-surface-hover-dark)] cursor-pointer transition-colors {{ $selectedDocumentId == $doc->id ? 'bg-indigo-50/50 dark:bg-indigo-900/10 ring-1 ring-inset ring-indigo-500/20' : '' }}">

                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <i class="fas {{ $this->getIconFor($doc->extension) }} text-base"></i>
                                                <div class="flex flex-col">
                                                    <span
                                                        class="font-bold text-[var(--color-text-main)] dark:text-[var(--color-text-main-dark)] group-hover:text-indigo-600 transition-colors">{{ $doc->name }}</span>
                                                    <span
                                                        class="text-[0.6rem] text-slate-400 uppercase font-bold tracking-tight">{{ $doc->extension }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td
                                            class="px-4 py-3 text-[var(--color-text-muted)] dark:text-[var(--color-text-muted-dark)] font-medium">
                                            {{ $doc->ownerUser->name ?? 'System' }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-400 whitespace-nowrap">
                                            {{ $doc->created_at->format('M d, Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-400 font-medium">
                                            {{ number_format($doc->size / 1024, 1) }} KB
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div
                                                class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button title="View"
                                                    class="w-6 h-6 rounded flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-all">
                                                    <i class="fas fa-eye text-[0.65rem]"></i>
                                                </button>
                                                <a href="{{ asset($doc->file_path) }}" download title="Download"
                                                    class="w-6 h-6 rounded flex items-center justify-center text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition-all">
                                                    <i class="fas fa-download text-[0.65rem]"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif
        </div>

        {{-- ── PROPERTIES SIDEBAR ────────────────────────────────────────────── --}}
        <aside x-show="detailVisible" x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="w-72 sm:w-80 border-l border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] overflow-y-auto z-20 shadow-[-10px_0_15px_-3px_rgba(0,0,0,0.05)] flex flex-col p-4 gap-6 shrink-0 h-full">

            @if($selectedDoc)
                    {{-- Header / Icon --}}
                    <div class="flex flex-col items-center pt-2">
                        <div class="relative group">
                            <div
                                class="w-20 h-20 bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl flex items-center justify-center mb-4 transition-transform group-hover:scale-110 duration-300">
                                <i class="fas {{ $this->getIconFor($selectedDoc->extension) }} text-4xl shadow-sm"></i>
                            </div>
                            <div
                                class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full border-2 border-white dark:border-slate-800 flex items-center justify-center shadow-lg {{ $editingVisibility === 'public' ? 'bg-emerald-500' : 'bg-rose-500' }}">
                                <i
                                    class="fas {{ $editingVisibility === 'public' ? 'fa-unlock' : 'fa-lock' }} text-[0.6rem] text-white"></i>
                            </div>
                        </div>

                        <input type="text" wire:model.blur="editingName"
                            class="text-center w-full font-bold text-sm bg-transparent border-0 border-b border-transparent focus:border-indigo-500 focus:ring-0 p-1 mb-1 transition-all text-[var(--color-text-main)] dark:text-[var(--color-text-main-dark)]"
                            placeholder="Enter name...">

                        <span
                            class="text-[0.6rem] uppercase text-slate-400 font-extrabold tracking-widest bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full">
                            {{ $selectedDoc->extension ?: 'Unknown' }}
                        </span>
                    </div>

                    {{-- Detail Fields --}}
                    <div class="space-y-5">

                        {{-- Access Control --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Access
                                Control</label>
                            <button wire:click="toggleVisibility"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl border text-[0.7rem] font-bold transition-all group
                                               {{ $editingVisibility === 'public'
                ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/10 dark:border-emerald-800 dark:text-emerald-400'
                : 'border-amber-200 bg-amber-50 text-amber-700 dark:bg-amber-900/10 dark:border-amber-800 dark:text-amber-400' }}">
                                <div class="flex items-center gap-2">
                                    <i
                                        class="fas {{ $editingVisibility === 'public' ? 'fa-unlock-keyhole' : 'fa-lock' }} text-sm opacity-80 group-hover:scale-110 transition-transform"></i>
                                    <span>{{ ucfirst($editingVisibility) }} Access</span>
                                </div>
                                <i class="fas fa-rotate text-[0.6rem] opacity-40"></i>
                            </button>
                        </div>

                        {{-- Classification --}}
                        <div class="flex flex-col gap-1.5">
                            <label
                                class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Workspace</label>
                            <select wire:model.live="currentFolderId"
                                class="w-full text-xs font-semibold rounded-xl border border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)] bg-[var(--color-surface-muted)] dark:bg-[var(--color-surface-muted-dark)] text-[var(--color-text-main)] dark:text-[var(--color-text-main-dark)] py-2 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-none">
                                @foreach($allFolders as $f)
                                    <option value="{{ $f->id }}">{{ $f->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Owner --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Custodian
                            </label>
                            <select wire:model.live="editingOwner"
                                class="w-full text-xs font-semibold rounded-xl border border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)] bg-[var(--color-surface-muted)] dark:bg-[var(--color-surface-muted-dark)] text-[var(--color-text-main)] dark:text-[var(--color-text-main-dark)] py-2 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-none">
                                @foreach($owners as $owner)
                                    <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Metadata Info --}}
                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <div class="flex flex-col">
                                <span class="text-[0.6rem] text-slate-400 font-bold uppercase tracking-tighter">Size</span>
                                <span
                                    class="text-[0.7rem] font-bold text-[var(--color-text-main)] dark:text-[var(--color-text-main-dark)]">
                                    {{ number_format($selectedDoc->size / 1024, 1) }} KB
                                </span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[0.6rem] text-slate-400 font-bold uppercase tracking-tighter">Added On</span>
                                <span
                                    class="text-[0.7rem] font-bold text-[var(--color-text-main)] dark:text-[var(--color-text-main-dark)]">
                                    {{ $selectedDoc->created_at->format('M d, Y') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Action Bar --}}
                    <div
                        class="mt-auto pt-4 border-t border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)] space-y-2">
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ asset($selectedDoc->file_path) }}" download
                                class="flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-indigo-600 text-white text-[0.7rem] font-bold hover:bg-indigo-700 transition-all shadow-md shadow-indigo-500/10">
                                <i class="fas fa-download text-[0.65rem]"></i>
                                <span>Download</span>
                            </a>
                            <button wire:click="openShareModal"
                                class="flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[0.7rem] font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                                <i class="fas fa-share-alt text-[0.65rem]"></i>
                                <span>Share</span>
                            </button>
                        </div>

                        <button wire:click="deleteDocument('{{ $selectedDoc->id }}')"
                            wire:confirm="Permanently delete this document from the workspace?"
                            class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 text-[0.7rem] font-bold transition-all border border-transparent hover:border-rose-100 dark:hover:border-rose-900/30">
                            <i class="fas fa-trash-alt text-[0.65rem]"></i>
                            <span>Delete Forever</span>
                        </button>
                    </div>
            @endif
        </aside>
    </div>

    {{-- ── SHARING MODAL ─────────────────────────────────────────────────── --}}
    <x-ts-modal wire:model="showShareModal" title="Share Document" size="md">
        <div class="space-y-4">
            <div class="p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl border border-indigo-100 dark:border-indigo-900/30">
                <p class="text-xs text-indigo-700 dark:text-indigo-400 font-bold mb-1">Sharing Link</p>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="{{ $shareUrl }}"
                        class="flex-1 bg-transparent border-0 text-[0.7rem] font-mono text-slate-600 dark:text-slate-400 focus:ring-0 truncate">
                    <x-ts-button x-on:click="$clipboard('{{ $shareUrl }}'); $dispatch('ts-toast', {type: 'success', text: 'Copied to clipboard'})"
                        color="indigo" variant="ghost" icon="clipboard" sm />
                </div>
            </div>

            <x-ts-input wire:model="shareName" label="Display Name" />
            
            <div class="flex items-center justify-between gap-4">
                <div class="flex-1">
                    <p class="text-xs font-bold text-slate-900 dark:text-white">Public Link</p>
                    <p class="text-[0.65rem] text-slate-500">Anyone with the link can view.</p>
                </div>
                <x-ts-toggle wire:model="shareVisibility" lg
                    on-value="public" off-value="private" />
            </div>
        </div>
        <x-slot:footer>
            <div class="flex justify-end gap-2">
                <x-ts-button color="slate" variant="ghost" x-on:click="show = false">Cancel</x-ts-button>
                <x-ts-button color="indigo" wire:click="confirmShare">Finalize Share</x-ts-button>
            </div>
        </x-slot:footer>
    </x-ts-modal>

    {{-- ── FILE REQUEST MODAL ────────────────────────────────────────────── --}}
    <x-ts-modal wire:model="showRequestModal" title="Request Files" size="md">
        <div class="space-y-4">
            <x-ts-input wire:model="requestTo" label="Recipient Email" placeholder="client@example.com" />
            <x-ts-input wire:model="requestName" label="Request Name" placeholder="Invoice May 2024" />
            <x-ts-textarea wire:model="requestNote" label="Instruction (Optional)" rows="3" />
            <x-ts-input type="date" wire:model="requestDueDate" label="Due Date" />
        </div>
        <x-slot:footer>
            <div class="flex justify-end gap-2">
                <x-ts-button color="slate" variant="ghost" x-on:click="show = false">Cancel</x-ts-button>
                <x-ts-button color="indigo" wire:click="submitFileRequest">Send Request</x-ts-button>
            </div>
        </x-slot:footer>
    </x-ts-modal>
</div>