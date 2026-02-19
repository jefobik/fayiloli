@if (!Route::is('home'))
<div class="edms-toolbar">

    @if (Route::is('documents.index'))
        {{-- ── Document page toolbar ─────────────────────────────────── --}}
        <button class="toolbar-btn toolbar-btn-outline" onclick="openModal('uploadFolderModal')">
            <i class="fas fa-folder-plus"></i> Upload Folder
        </button>
        <button class="toolbar-btn toolbar-btn-primary" onclick="uploadFiles()">
            <i class="fas fa-upload"></i> Upload Files
        </button>
        <button class="toolbar-btn toolbar-btn-outline" onclick="openModal('uploadModal')">
            <i class="fas fa-link"></i> Add URL
        </button>

        <div class="toolbar-divider"></div>

        <button class="toolbar-btn toolbar-btn-outline"
                onclick="requestDocument()"
                data-bs-toggle="modal" data-bs-target="#requestDocumentModal">
            <i class="fas fa-file-import"></i> Request
        </button>
        <button class="toolbar-btn toolbar-btn-outline"
                data-bs-toggle="modal"
                onclick="shareDocument()"
                data-bs-target="#shareDocumentModal">
            <i class="fas fa-share-alt"></i> Share
        </button>

    @elseif(!Route::is('getSharedDocuments'))
        {{-- ── Folder / workspace page toolbar ──────────────────────── --}}
        <button class="toolbar-btn toolbar-btn-primary" onclick="openModal('createFolderModal')">
            <i class="fas fa-plus"></i> New Folder
        </button>
        <button class="toolbar-btn toolbar-btn-outline" onclick="downloadFolder()">
            <i class="fas fa-cloud-download-alt"></i> Download
        </button>
        <button class="toolbar-btn toolbar-btn-danger" id="delete-button" onclick="deleteSelectedRecord()">
            <i class="fas fa-trash-alt"></i> Delete
        </button>

    @elseif(isset($shareDocument) && $shareDocument->slug === 'folder')
        {{-- ── Shared folder toolbar ─────────────────────────────────── --}}
        <button class="toolbar-btn toolbar-btn-outline" onclick="downloadFolder()">
            <i class="fas fa-cloud-download-alt"></i> Download
        </button>
    @endif

</div>
@endif
