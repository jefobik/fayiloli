{{--
  Workspace / Folder management table.
  All jQuery UI sortable hooks preserved:
    - #folders-table tbody           → sortable container
    - .fa-arrows                     → drag handle class
    - .tree-parent[data-id]          → parent row
    - .tree-child[data-id]           → child row
    - .child-list / .subfolder-list / .category-list  → nested lists
    - toggleChildRows(), orderFolder(), orderChildFolder(), deleteSelectedFolders()
--}}
<div id="error-message" class="mb-2" role="alert" aria-live="polite"></div>

@if ($folders->isNotEmpty())

    <div class="table-responsive overflow-y-auto rounded-b-xl scrollbar-thin
                scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600"
         style="max-height: 65vh">
        <table class="table table-hover align-middle mb-0 w-full text-sm" id="folders-table"
               aria-label="Workspaces list">

            {{-- ── Header ──────────────────────────────────────────────── --}}
            <thead class="sticky top-0 z-10 shadow-sm">
                <tr class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                    {{-- Checkbox --}}
                    <th scope="col"
                        class="ps-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider w-10">
                        <input type="checkbox" id="checkAll" class="form-check-input"
                               aria-label="Select all workspaces">
                    </th>
                    {{-- Workspace --}}
                    <th scope="col"
                        class="py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <div class="flex items-center gap-1.5">
                            <i class="fas fa-folder text-amber-400 text-xs" aria-hidden="true"></i>
                            Workspaces
                        </div>
                    </th>
                    {{-- Tag Categories --}}
                    <th scope="col"
                        class="py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden sm:table-cell">
                        <div class="flex items-center gap-1.5">
                            <i class="fas fa-tags text-indigo-400 text-xs" aria-hidden="true"></i>
                            Tag Categories
                        </div>
                    </th>
                    {{-- Actions (blank header, aligned right) --}}
                    <th scope="col"
                        class="py-3 pe-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider w-16">
                        <span class="sr-only">Drag</span>
                    </th>
                </tr>
            </thead>

            {{-- ── Body ────────────────────────────────────────────────── --}}
            <tbody>
                @foreach ($folders as $folder)
                    <tr class="tree-parent group hover:bg-slate-50 dark:hover:bg-slate-800/50
                               transition-colors border-b border-slate-100 dark:border-slate-800 last:border-0"
                        data-id="{{ $folder->id }}">

                        {{-- Checkbox --}}
                        <td class="ps-4">
                            <input type="checkbox" name="folder_ids[]" value="{{ $folder->id }}"
                                   class="form-check-input" aria-label="Select {{ $folder->name }}">
                        </td>

                        {{-- Workspace name + tree ────────────────────────── --}}
                        <td class="py-2.5 pr-3">
                            <div class="flex items-center gap-2">
                                {{-- Expand/collapse caret --}}
                                @if ($folder->subfolders->isNotEmpty())
                                    <button type="button"
                                            class="w-5 h-5 flex items-center justify-center rounded text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors shrink-0"
                                            onclick="toggleChildRows(this)"
                                            aria-expanded="false"
                                            aria-label="Expand {{ $folder->name }}">
                                        <i class="fa fa-caret-right toggle-icon text-xs" aria-hidden="true"></i>
                                    </button>
                                @else
                                    <span class="w-5 shrink-0"></span>
                                @endif

                                {{-- Folder icon --}}
                                <i class="fas fa-folder text-amber-400 shrink-0" aria-hidden="true"></i>

                                {{-- Folder name --}}
                                <span class="font-semibold text-slate-900 dark:text-white folder-name">
                                    {{ $folder->name }}
                                </span>
                            </div>

                            {{-- Nested subfolders (hidden by default, toggled by jQuery) --}}
                            <ul class="child-list mt-1 space-y-0.5" style="display: none">
                                @foreach ($folder->subfolders as $subfolder)
                                    <li class="tree-child flex items-start gap-2 py-1 px-2 ml-6 rounded hover:bg-slate-50 dark:hover:bg-slate-800/50"
                                        data-id="{{ $subfolder->id }}">
                                        <input type="checkbox" name="folder_ids[]" value="{{ $subfolder->id }}"
                                               class="form-check-input mt-0.5 shrink-0"
                                               aria-label="Select {{ $subfolder->name }}">
                                        <i class="fa fa-arrows text-slate-300 dark:text-slate-600 cursor-grab mt-0.5 shrink-0 text-xs" title="Drag to reorder" aria-hidden="true"></i>
                                        <i class="fas fa-folder text-amber-300 shrink-0 text-sm" aria-hidden="true"></i>
                                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ $subfolder->name }}</span>

                                        {{-- Sub-subfolders --}}
                                        <ul class="subfolder-list w-full mt-1 space-y-0.5">
                                            @foreach ($subfolder->subfolders as $subsubfolder)
                                                <li class="tree-child flex items-center gap-2 py-0.5 px-2 ml-4 rounded hover:bg-slate-50 dark:hover:bg-slate-800/50"
                                                    data-id="{{ $subsubfolder->id }}">
                                                    <input type="checkbox" name="folder_ids[]" value="{{ $subsubfolder->id }}"
                                                           class="form-check-input shrink-0">
                                                    <i class="fa fa-arrows text-slate-300 dark:text-slate-600 cursor-grab shrink-0 text-xs" title="Drag to reorder" aria-hidden="true"></i>
                                                    <i class="fas fa-folder text-amber-200 shrink-0 text-xs" aria-hidden="true"></i>
                                                    <span class="text-xs text-slate-600 dark:text-slate-400">{{ $subsubfolder->name }}</span>
                                                    <ul class="category-list flex flex-wrap gap-1 mt-0.5 ml-1">
                                                        @foreach ($subsubfolder->categories as $category)
                                                            <li>
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.6rem] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                                                    {{ $category->name }}
                                                                </span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </li>
                                            @endforeach
                                        </ul>

                                        <ul class="category-list flex flex-wrap gap-1 mt-0.5 ml-1">
                                            @foreach ($subfolder->categories as $category)
                                                <li>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.6rem] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                                        {{ $category->name }}
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </td>

                        {{-- Tag categories (top-level) ────────────────────── --}}
                        <td class="py-2.5 pr-3 hidden sm:table-cell">
                            <div class="flex flex-wrap gap-1">
                                @forelse ($folder->categories as $category)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[0.65rem] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                        {{ $category->name }}
                                    </span>
                                @empty
                                    <span class="text-xs text-slate-300 dark:text-slate-600">—</span>
                                @endforelse
                            </div>
                        </td>

                        {{-- Drag handle ──────────────────────────────────── --}}
                        <td class="py-2.5 pe-4 text-right">
                            <i class="fa fa-arrows text-slate-300 hover:text-indigo-500 dark:text-slate-600 dark:hover:text-indigo-400 cursor-grab text-sm transition-colors"
                               title="Drag to reorder" aria-hidden="true"></i>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@else
    <x-notFound
        icon="fas fa-folder-open"
        title="No Workspaces Yet"
        message="Create your first workspace to start organising documents."
        actionText="Create Workspace" />
@endif


{{-- jQuery UI — sortable drag-and-drop ──────────────────────────────────── --}}
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

<script>
    /* ── Check All / individual checkboxes ───────────────────────────────── */
    $(document).ready(function () {
        $('#checkAll').change(function () {
            $('input[name="folder_ids[]"]').prop('checked', $(this).prop('checked'));
            updateDeleteButton();
        });
        $('input[name="folder_ids[]"]').change(function () {
            if (!$(this).prop('checked')) {
                $('#checkAll').prop('checked', false);
            } else if ($('input[name="folder_ids[]"]:checked').length === $('input[name="folder_ids[]"]').length) {
                $('#checkAll').prop('checked', true);
            }
            updateDeleteButton();
        });
    });

    /* ── Toggle nested subfolders ────────────────────────────────────────── */
    function toggleChildRows(icon) {
        var parentRow = $(icon).closest('.tree-parent');
        var childList = parentRow.find('.child-list');
        childList.slideToggle(150);
        $(icon).find('.toggle-icon').toggleClass('fa-caret-right fa-caret-down');
        $(icon).attr('aria-expanded', childList.is(':visible') ? 'true' : 'false');
    }

    /* ── Sortable setup ──────────────────────────────────────────────────── */
    window.setupSortableLists = function () {
        $('#folders-table tbody').sortable({
            handle: '.fa-arrows',
            placeholder: 'holder',
            tolerance: 'pointer',
            revert: 200,
            forcePlaceholderSize: true,
            opacity: 0.7,
            scroll: true,
            items: '> .tree-parent',
            update: function () { orderFolder(); }
        });
        $('.child-list').sortable({
            handle: '.fa-arrows',
            placeholder: 'holder',
            tolerance: 'pointer',
            revert: 200,
            forcePlaceholderSize: true,
            opacity: 0.7,
            scroll: true,
            connectWith: '.child-list',
            update: function (event, ui) {
                orderChildFolder($(this).closest('.tree-parent'));
            }
        });
    };

    function orderFolder() {
        var positions = {};
        $('#folders-table tbody > .tree-parent').each(function (index) {
            positions[$(this).data('id')] = index + 1;
        });
        $.ajax({
            url: '{{ route('folders.updatePositions') }}',
            method: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content'), positions: positions },
            error: function (xhr) { $('#error-message').html('<div class="p-2 text-sm text-red-600 bg-red-50 rounded-lg border border-red-100">' + (xhr.responseText || 'Server error') + '</div>'); }
        });
    }

    function orderChildFolder(parentRow) {
        var parentId = parentRow.data('id');
        var positions = {};
        parentRow.find('.child-list > .tree-child').each(function (index) {
            positions[$(this).data('id')] = index + 1;
        });
        $.ajax({
            url: '{{ route('folders.updateChildPositions') }}',
            method: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content'), parent_id: parentId, positions: positions },
            error: function (xhr) { $('#error-message').html('<div class="p-2 text-sm text-red-600 bg-red-50 rounded-lg border border-red-100">Server error: ' + xhr.responseText + '</div>'); }
        });
    }

    /* ── Delete button visibility ────────────────────────────────────────── */
    function updateDeleteButton() {
        if ($('input[name="folder_ids[]"]:checked').length > 0) {
            $('#delete-button').show();
            $('#error-message').html('');
        } else {
            $('#delete-button').hide();
        }
    }

    function deleteSelectedRecord() { deleteSelectedFolders(); }

    function deleteSelectedFolders() {
        var folderIds = $('input[name="folder_ids[]"]:checked').map(function () { return $(this).val(); }).get();
        $.ajax({
            url: '{{ route('folders.deleteSelecetdFolder') }}',
            method: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content'), folder_ids: folderIds },
            success: function (response) {
                $('#error-message').html('<div class="p-2 text-sm text-emerald-700 bg-emerald-50 rounded-lg border border-emerald-100">' + (response.message || 'Deleted.') + '</div>');
                $('#renderFolderTableHtml').html(response.html);
                localStorage.setItem('selectedFolderId', '');
                localStorage.setItem('selectedShareDocumentFolder', '');
            },
            error: function (xhr) {
                $('#error-message').html('<div class="p-2 text-sm text-red-600 bg-red-50 rounded-lg border border-red-100">' + (xhr.responseJSON?.errors || 'Error deleting folders') + '</div>');
            }
        });
    }

    /* ── Download selected as ZIP ────────────────────────────────────────── */
    function downloadFolder() {
        var folderIds = $('input[name="folder_ids[]"]:checked').map(function () { return $(this).val(); }).get();
        $.ajax({
            url: '/folders/details',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', folder_ids: folderIds },
            success: function (response) {
                $.ajax({
                    url: '/folders/download-zip',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', folders: response.folders },
                    xhrFields: { responseType: 'blob' },
                    success: function (data) {
                        var blob = new Blob([data], { type: 'application/zip' });
                        var url  = window.URL.createObjectURL(blob);
                        var link = document.createElement('a');
                        link.href = url;
                        link.download = 'WorkspaceExport.zip';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        window.URL.revokeObjectURL(url);
                    },
                    error: function (xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.error : 'Error generating zip — the folder may be empty.';
                        $('#error-message').html('<div class="p-2 text-sm text-red-600 bg-red-50 rounded-lg border border-red-100">' + msg + '</div>');
                    }
                });
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    for (var key in errors) {
                        $('#error-message').html('<div class="p-2 text-sm text-red-600 bg-red-50 rounded-lg border border-red-100">' + errors[key][0] + '</div>');
                    }
                }
            }
        });
    }

    $(function () {
        setupSortableLists();
        updateDeleteButton();
    });
</script>
