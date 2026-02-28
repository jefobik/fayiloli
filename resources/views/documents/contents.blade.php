{{--
  Document contents — rendered by the server and injected via AJAX into
  #renderDocumentContentHtml. The parent div (#documentContent) carries
  a [data-view="grid|list"] attribute set by Alpine in documents/index.blade.php.
  CSS in documents12.css uses that attribute to show/hide the correct view.

  JS contracts preserved:
    - .card-content[data-id][data-name][data-url][data-path]
      [data-folder][data-owner][data-contact][data-img]
      [data-visibility][data-file_type]   ← read by selectCard()
    - #sortable                            ← jQuery UI sortable
    - .documentContentClass               ← referenced in documents1001.js
    - previewCourseFile(ext, url)          ← triggered on thumbnail click
--}}

{{-- ══════════════════════════════════════════════════════════════
     GRID VIEW
════════════════════════════════════════════════════════════════ --}}
<div class="doc-grid-view" id="sortable" role="list" aria-label="Documents grid">
    @forelse ($documents as $document)
        @php
            $ext   = $document->extension ?? '';
            $isImg = in_array(strtolower($ext), ['jpg','jpeg','png','gif','webp','svg','bmp','tiff']);
            $isVid = in_array(strtolower($ext), ['mp4','webm','ogg','mov']);
            $iconMap = [
                'pdf'  => ['fa-file-pdf',   '#ef4444', '#fee2e2'],
                'doc'  => ['fa-file-word',  '#3b82f6', '#dbeafe'],
                'docx' => ['fa-file-word',  '#3b82f6', '#dbeafe'],
                'xls'  => ['fa-file-excel', '#22c55e', '#dcfce7'],
                'xlsx' => ['fa-file-excel', '#22c55e', '#dcfce7'],
                'ppt'  => ['fa-file-powerpoint','#f97316','#ffedd5'],
                'pptx' => ['fa-file-powerpoint','#f97316','#ffedd5'],
                'zip'  => ['fa-file-zipper','#8b5cf6', '#ede9fe'],
                'rar'  => ['fa-file-zipper','#8b5cf6', '#ede9fe'],
                'mp3'  => ['fa-file-audio', '#06b6d4', '#cffafe'],
                'mp4'  => ['fa-file-video', '#6366f1', '#e0e7ff'],
                'txt'  => ['fa-file-lines', '#64748b', '#f1f5f9'],
                'csv'  => ['fa-file-csv',   '#10b981', '#d1fae5'],
                'json' => ['fa-file-code',  '#f59e0b', '#fef3c7'],
                'xml'  => ['fa-file-code',  '#f59e0b', '#fef3c7'],
            ];
            [$iconClass, $iconColor, $iconBg] = $iconMap[strtolower($ext)] ?? ['fa-file', '#6366f1', '#e0e7ff'];
            $isPublic = $document->visibility === 'public';
        @endphp

        <div class="doc-card documentContentClass group relative bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)]
                    rounded-xl border border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)]
                    hover:border-indigo-300 dark:hover:border-indigo-700
                    hover:shadow-md dark:hover:shadow-slate-900/30
                    transition-all duration-200 cursor-pointer overflow-hidden
                    flex flex-col"
             id="{{ $document->id }}"
             draggable="true"
             role="listitem">

            {{-- Thumbnail / preview area ──────────────────────────────── --}}
            <div class="{{ $isImg ? 'img-container-file' : 'img-container' }}
                        relative flex items-center justify-center
                        bg-[var(--color-surface-muted)] dark:bg-[var(--color-surface-muted-dark)]
                        border-b border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)]
                        overflow-hidden cursor-zoom-in aspect-video"
                 onclick="previewCourseFile('{{ $ext }}', '{{ asset($document->file_path) }}')"
                 data-previewFile="{{ $document->getFileIcon() }}"
                 title="Preview {{ $document->name }}"
                 aria-label="Preview {{ $document->name }}">

                @if ($isImg)
                    <img src="{{ asset($document->file_path) }}"
                         alt="{{ $document->name }}"
                         class="w-full h-full object-cover"
                         loading="lazy"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                    {{-- Fallback icon if image fails to load --}}
                    <div class="hidden w-full h-full items-center justify-center"
                         style="background:{{ $iconBg }}">
                        <i class="fas {{ $iconClass }} text-3xl" style="color:{{ $iconColor }}" aria-hidden="true"></i>
                    </div>
                @else
                    <div class="flex flex-col items-center gap-1.5"
                         style="background:{{ $iconBg }};padding:1.5rem;width:100%;height:100%;justify-content:center">
                        <i class="fas {{ $iconClass }} text-4xl drop-shadow-sm"
                           style="color:{{ $iconColor }}" aria-hidden="true"></i>
                        @if ($ext)
                            <span class="text-[0.65rem] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded"
                                  style="color:{{ $iconColor }};background:rgba(255,255,255,0.6)">
                                {{ strtoupper($ext) }}
                            </span>
                        @endif
                    </div>
                    <img src="{{ $document->getFileIcon() }}" width="0" height="0" alt="" id="filePreviewId" style="display:none">
                @endif

                {{-- Visibility badge --}}
                <div class="absolute top-1.5 right-1.5">
                    @if ($isPublic)
                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[0.6rem] font-bold bg-emerald-100/90 text-emerald-700 border border-emerald-200/50 backdrop-blur-sm" title="Public">
                            <i class="fa fa-unlock text-[0.55rem]" aria-hidden="true"></i>
                            <span class="hidden sm:inline">Public</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[0.6rem] font-bold bg-rose-100/90 text-rose-700 border border-rose-200/50 backdrop-blur-sm" title="Private">
                            <i class="fa fa-lock text-[0.55rem]" aria-hidden="true"></i>
                        </span>
                    @endif
                </div>
            </div>

            {{-- Card content (metadata) — selectCard() reads data-* attributes ── --}}
            <div class="card-content flex-1 px-3 py-2.5"
                 onclick="selectCard(this)"
                 data-id="{{ $document->id }}"
                 data-name="{{ $document->name }}"
                 data-url="{{ $document->url }}"
                 data-path="{{ $document->file_path }}"
                 data-folder="{{ $document->folder_id }}"
                 data-owner="{{ $document->owner }}"
                 data-contact="{{ $document->contact }}"
                 data-img="{{ $document->getFileIcon() }}"
                 data-visibility="{{ $document->isPublic() }}"
                 data-file_type="{{ $ext }}">

                {{-- Filename --}}
                <h4 class="card-title text-[0.8rem] font-bold text-[var(--color-text-main)] dark:text-[var(--color-text-main-dark)]
                           truncate leading-snug mb-1"
                    title="{{ $document->name }}">
                    {{ $document->name }}
                </h4>

                {{-- Tags --}}
                @if ($document->tags && $document->tags->isNotEmpty())
                    <div class="flex flex-wrap gap-1 mb-1.5">
                        @foreach ($document->tags as $tag)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[0.6rem] font-bold"
                                  style="background-color:{{ $tag->background_color ?? '#6366f1' }};
                                         color:{{ $tag->foreground_color ?? '#ffffff' }}">
                                {{ $tag->name }}
                            </span>
                        @endforeach
                    </div>
                @endif

                {{-- Date --}}
                <div class="date flex items-center gap-1 text-[0.68rem] font-medium text-[var(--color-text-muted)] dark:text-[var(--color-text-muted-dark)] mt-auto">
                    <i class="far fa-clock text-[0.6rem]" aria-hidden="true"></i>
                    <span>{{ $document->created_at?->format('d M Y') }}</span>
                    <div class="emojis ml-auto">
                        <x-avatar width="16" height="16" />
                    </div>
                </div>
            </div>

            {{-- Hover action bar ──────────────────────────────────────── --}}
            <div class="absolute bottom-0 left-0 right-0 flex items-center justify-end gap-0.5 px-2 py-1.5
                        bg-[var(--color-surface)]/95 dark:bg-[var(--color-surface-dark)]/95 backdrop-blur-sm
                        border-t border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)]
                        opacity-0 group-hover:opacity-100 transition-opacity duration-150
                        pointer-events-none group-hover:pointer-events-auto"
                 role="group" aria-label="Actions for {{ $document->name }}">
                <button type="button"
                        class="w-6 h-6 flex items-center justify-center rounded text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors"
                        title="Preview" onclick="event.stopPropagation(); previewCourseFile('{{ $ext }}', '{{ asset($document->file_path) }}')">
                    <i class="fas fa-eye text-[0.65rem]" aria-hidden="true"></i>
                </button>
                <button type="button"
                        class="w-6 h-6 flex items-center justify-center rounded text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition-colors"
                        title="Share" onclick="event.stopPropagation(); shareDocument()">
                    <i class="fas fa-share-alt text-[0.65rem]" aria-hidden="true"></i>
                </button>
                <a href="{{ asset($document->file_path) }}" download="{{ $document->name }}"
                   class="w-6 h-6 flex items-center justify-center rounded text-slate-400 hover:text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-900/30 transition-colors"
                   title="Download" onclick="event.stopPropagation()">
                    <i class="fas fa-download text-[0.65rem]" aria-hidden="true"></i>
                </a>
            </div>

        </div>{{-- /doc-card --}}

    @empty
        <div class="col-md-12 mb-3">
            <x-notFound
                icon="far fa-file"
                title="No Documents in This Workspace"
                actionText="Upload Document"
                actionUrl="#" />
        </div>
    @endforelse
</div>{{-- /doc-grid-view --}}


{{-- ══════════════════════════════════════════════════════════════
     LIST VIEW
════════════════════════════════════════════════════════════════ --}}
<div class="doc-list-view" role="table" aria-label="Documents list" style="display:none">
    @if ($documents->isNotEmpty())
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="sticky top-0 z-10 bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] border-b border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)]">
                    <th scope="col" class="py-2.5 pl-4 pr-2 text-left text-[0.7rem] font-bold text-[var(--color-text-muted)] dark:text-[var(--color-text-muted-dark)] uppercase tracking-wider w-8">
                        <span class="sr-only">Select</span>
                    </th>
                    <th scope="col" class="py-2.5 px-3 text-left text-[0.7rem] font-bold text-[var(--color-text-muted)] dark:text-[var(--color-text-muted-dark)] uppercase tracking-wider">Name</th>
                    <th scope="col" class="py-2.5 px-3 text-left text-[0.7rem] font-bold text-[var(--color-text-muted)] dark:text-[var(--color-text-muted-dark)] uppercase tracking-wider hidden md:table-cell">Tags</th>
                    <th scope="col" class="py-2.5 px-3 text-left text-[0.7rem] font-bold text-[var(--color-text-muted)] dark:text-[var(--color-text-muted-dark)] uppercase tracking-wider hidden sm:table-cell w-20">Access</th>
                    <th scope="col" class="py-2.5 px-3 text-left text-[0.7rem] font-bold text-[var(--color-text-muted)] dark:text-[var(--color-text-muted-dark)] uppercase tracking-wider hidden lg:table-cell w-28">Date</th>
                    <th scope="col" class="py-2.5 pl-2 pr-4 text-right text-[0.7rem] font-bold text-[var(--color-text-muted)] dark:text-[var(--color-text-muted-dark)] uppercase tracking-wider w-20">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($documents as $document)
                    @php
                        $ext     = $document->extension ?? '';
                        $isImg   = in_array(strtolower($ext), ['jpg','jpeg','png','gif','webp','svg','bmp','tiff']);
                        $isPublic = $document->visibility === 'public';
                        $iconMap2 = [
                            'pdf'  => ['fa-file-pdf',   'text-red-500'],
                            'doc'  => ['fa-file-word',  'text-blue-500'],
                            'docx' => ['fa-file-word',  'text-blue-500'],
                            'xls'  => ['fa-file-excel', 'text-green-500'],
                            'xlsx' => ['fa-file-excel', 'text-green-500'],
                            'ppt'  => ['fa-file-powerpoint','text-orange-500'],
                            'pptx' => ['fa-file-powerpoint','text-orange-500'],
                            'zip'  => ['fa-file-zipper','text-violet-500'],
                            'rar'  => ['fa-file-zipper','text-violet-500'],
                            'mp3'  => ['fa-file-audio', 'text-cyan-500'],
                            'mp4'  => ['fa-file-video', 'text-indigo-500'],
                            'txt'  => ['fa-file-lines', 'text-slate-400'],
                        ];
                        [$listIcon, $listColor] = $iconMap2[strtolower($ext)] ?? ($isImg ? ['fa-file-image','text-pink-500'] : ['fa-file','text-indigo-400']);
                    @endphp
                    <tr class="group card-content documentContentClass border-b border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)]
                               hover:bg-[var(--color-surface-hover)] dark:hover:bg-[var(--color-surface-hover-dark)] transition-colors
                               cursor-pointer last:border-0"
                        onclick="selectCard(this)"
                        data-id="{{ $document->id }}"
                        data-name="{{ $document->name }}"
                        data-url="{{ $document->url }}"
                        data-path="{{ $document->file_path }}"
                        data-folder="{{ $document->folder_id }}"
                        data-owner="{{ $document->owner }}"
                        data-contact="{{ $document->contact }}"
                        data-img="{{ $document->getFileIcon() }}"
                        data-visibility="{{ $document->isPublic() }}"
                        data-file_type="{{ $ext }}">

                        {{-- File icon --}}
                        <td class="py-2 pl-4 pr-2">
                            <i class="fas {{ $listIcon }} {{ $listColor }} text-base" aria-hidden="true"></i>
                        </td>

                        {{-- Name --}}
                        <td class="py-2 px-3">
                            <div class="font-semibold text-[var(--color-text-main)] dark:text-[var(--color-text-main-dark)] text-sm truncate max-w-[200px] sm:max-w-xs"
                                 title="{{ $document->name }}">
                                {{ $document->name }}
                            </div>
                            @if ($ext)
                                <div class="text-[0.68rem] text-slate-400 uppercase font-medium mt-0.5">
                                    {{ strtoupper($ext) }}
                                </div>
                            @endif
                        </td>

                        {{-- Tags --}}
                        <td class="py-2 px-3 hidden md:table-cell">
                            @if ($document->tags && $document->tags->isNotEmpty())
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($document->tags->take(3) as $tag)
                                        <span class="px-1.5 py-0.5 rounded-full text-[0.6rem] font-bold"
                                              style="background-color:{{ $tag->background_color ?? '#6366f1' }};
                                                     color:{{ $tag->foreground_color ?? '#fff' }}">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                    @if ($document->tags->count() > 3)
                                        <span class="text-[0.68rem] text-slate-400 font-medium">+{{ $document->tags->count() - 3 }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-slate-300 dark:text-slate-600 text-xs">—</span>
                            @endif
                        </td>

                        {{-- Visibility --}}
                        <td class="py-2 px-3 hidden sm:table-cell">
                            @if ($isPublic)
                                <span class="inline-flex items-center gap-1 text-[0.65rem] font-bold text-emerald-600 dark:text-emerald-400">
                                    <i class="fa fa-unlock text-[0.6rem]" aria-hidden="true"></i> Public
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[0.65rem] font-bold text-rose-500 dark:text-rose-400">
                                    <i class="fa fa-lock text-[0.6rem]" aria-hidden="true"></i> Private
                                </span>
                            @endif
                        </td>

                        {{-- Date --}}
                        <td class="py-2 px-3 hidden lg:table-cell">
                            <span class="text-xs font-medium text-[var(--color-text-muted)] dark:text-[var(--color-text-muted-dark)] whitespace-nowrap">
                                {{ $document->created_at?->format('d M Y') }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="py-2 pl-2 pr-4 text-right">
                            <div class="flex items-center justify-end gap-1
                                        opacity-0 group-hover:opacity-100 focus-within:opacity-100 transition-opacity">
                                <button type="button"
                                        class="w-6 h-6 flex items-center justify-center rounded text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors"
                                        title="Preview"
                                        onclick="event.stopPropagation(); previewCourseFile('{{ $ext }}', '{{ asset($document->file_path) }}')">
                                    <i class="fas fa-eye text-[0.65rem]" aria-hidden="true"></i>
                                </button>
                                <a href="{{ asset($document->file_path) }}" download="{{ $document->name }}"
                                   class="w-6 h-6 flex items-center justify-center rounded text-slate-400 hover:text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-900/30 transition-colors"
                                   title="Download" onclick="event.stopPropagation()">
                                    <i class="fas fa-download text-[0.65rem]" aria-hidden="true"></i>
                                </a>
                            </div>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <x-notFound
            icon="far fa-file"
            title="No Documents in This Workspace"
            actionText="Upload Document"
            actionUrl="#" />
    @endif
</div>{{-- /doc-list-view --}}


<script>
    /* ── jQuery UI sortable for grid view (document reordering) ──────────── */
    $(document).ready(function () {
        $("#sortable").sortable({
            containment: "parent",
            cursor: "move",
            update: function (event, ui) {
                updateDocumentOrder();
            }
        });

        function updateDocumentOrder() {
            var documentIds = $("#sortable").sortable("toArray");
            var selectedFolderId = localStorage.getItem('selectedFolderId');
            $.ajax({
                url: '/update-document-order',
                type: 'POST',
                data: { document_ids: documentIds, folder_id: selectedFolderId },
                headers: { 'X-CSRF-TOKEN': csrfToken },
                success: function () { /* silent */ },
                error: function (xhr, status, error) {
                    console.error('Error updating document order:', error);
                }
            });
        }
    });
</script>
