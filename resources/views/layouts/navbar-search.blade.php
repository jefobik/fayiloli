@if (!Route::is('home'))
<div class="edms-toolbar"
     role="toolbar"
     aria-label="{{ Route::is('documents.index') ? 'Document actions' : (isset($shareDocument) ? 'Shared folder actions' : 'Folder actions') }}">

    @if (Route::is('documents.index'))
        {{-- ── Document page toolbar ─────────────────────────────────── --}}
        <button type="button" class="toolbar-btn toolbar-btn-outline"
                onclick="openModal('uploadFolderModal')"
                aria-label="Upload folder">
            <i class="fas fa-folder-plus" aria-hidden="true"></i> Upload Folder
        </button>
        <button type="button" class="toolbar-btn toolbar-btn-primary"
                onclick="uploadFiles()"
                aria-label="Upload files">
            <i class="fas fa-upload" aria-hidden="true"></i> Upload Files
        </button>
        <button type="button" class="toolbar-btn toolbar-btn-outline"
                onclick="openModal('uploadModal')"
                aria-label="Add URL document">
            <i class="fas fa-link" aria-hidden="true"></i> Add URL
        </button>

        <div class="toolbar-divider" role="separator" aria-hidden="true"></div>

        <button type="button" class="toolbar-btn toolbar-btn-outline"
                onclick="requestDocument()"
                data-bs-toggle="modal" data-bs-target="#requestDocumentModal"
                aria-label="Request a document" aria-haspopup="dialog">
            <i class="fas fa-file-import" aria-hidden="true"></i> Request
        </button>
        <button type="button" class="toolbar-btn toolbar-btn-outline"
                data-bs-toggle="modal"
                onclick="shareDocument()"
                data-bs-target="#shareDocumentModal"
                aria-label="Share document" aria-haspopup="dialog">
            <i class="fas fa-share-alt" aria-hidden="true"></i> Share
        </button>

    @elseif(!Route::is('getSharedDocuments'))
        {{-- ── Folder / workspace page toolbar ──────────────────────── --}}
        <button type="button" class="toolbar-btn toolbar-btn-primary"
                onclick="openModal('createFolderModal')"
                aria-label="Create new folder" aria-haspopup="dialog">
            <i class="fas fa-plus" aria-hidden="true"></i> New Folder
        </button>
        <button type="button" class="toolbar-btn toolbar-btn-outline"
                onclick="downloadFolder()"
                aria-label="Download selected folder">
            <i class="fas fa-cloud-download-alt" aria-hidden="true"></i> Download
        </button>
        <button type="button" class="toolbar-btn toolbar-btn-danger"
                id="delete-button"
                onclick="deleteSelectedRecord()"
                aria-label="Delete selected items">
            <i class="fas fa-trash-alt" aria-hidden="true"></i> Delete
        </button>

    @elseif(isset($shareDocument) && $shareDocument->slug === 'folder')
        {{-- ── Shared folder toolbar ─────────────────────────────────── --}}
        <button type="button" class="toolbar-btn toolbar-btn-outline"
                onclick="downloadFolder()"
                aria-label="Download folder">
            <i class="fas fa-cloud-download-alt" aria-hidden="true"></i> Download
        </button>
    @endif

</div>
@endif
