@if (!Route::is('home'))
    <div class="flex flex-wrap items-center gap-2 mb-6" role="toolbar"
        aria-label="{{ Route::is('documents.index') ? 'Document actions' : (isset($shareDocument) ? 'Shared folder actions' : 'Folder actions') }}">

        @if (Route::is('documents.index'))
            {{-- ── Document page toolbar ─────────────────────────────────── --}}
            <x-ts-button color="slate" variant="outline" icon="folder-open" position="left"
                onclick="openModal('uploadFolderModal')" aria-label="Upload folder">
                Upload Folder
            </x-ts-button>
            <x-ts-button color="indigo" icon="arrow-up-tray" position="left" onclick="uploadFiles()" aria-label="Upload files">
                Upload Files
            </x-ts-button>
            <x-ts-button color="slate" variant="outline" icon="link" position="left" onclick="openModal('uploadModal')"
                aria-label="Add URL document">
                Add URL
            </x-ts-button>

            <div class="hidden sm:block h-6 w-px bg-slate-200 dark:bg-slate-700 mx-1" role="separator" aria-hidden="true"></div>

            <x-ts-button color="slate" variant="outline" icon="arrow-down-tray" position="left" onclick="requestDocument()"
                data-bs-toggle="modal" data-bs-target="#requestDocumentModal" aria-label="Request a document"
                aria-haspopup="dialog">
                Request
            </x-ts-button>
            <x-ts-button color="slate" variant="outline" icon="share" position="left" data-bs-toggle="modal"
                onclick="shareDocument()" data-bs-target="#shareDocumentModal" aria-label="Share document"
                aria-haspopup="dialog">
                Share
            </x-ts-button>

        @elseif(!Route::is('getSharedDocuments'))
            {{-- ── Folder / workspace page toolbar ──────────────────────── --}}
            <x-ts-button color="indigo" icon="plus" position="left" onclick="openModal('createFolderModal')"
                aria-label="Create new folder" aria-haspopup="dialog">
                New Folder
            </x-ts-button>
            <x-ts-button color="slate" variant="outline" icon="cloud-arrow-down" position="left" onclick="downloadFolder()"
                aria-label="Download selected folder">
                Download
            </x-ts-button>
            <x-ts-button color="red" variant="outline" icon="trash" position="left" id="delete-button"
                onclick="deleteSelectedRecord()" aria-label="Delete selected items">
                Delete
            </x-ts-button>

        @elseif(isset($shareDocument) && $shareDocument->slug === 'folder')
            {{-- ── Shared folder toolbar ─────────────────────────────────── --}}
            <x-ts-button color="slate" variant="outline" icon="cloud-arrow-down" position="left" onclick="downloadFolder()"
                aria-label="Download folder">
                Download
            </x-ts-button>
        @endif

    </div>
@endif