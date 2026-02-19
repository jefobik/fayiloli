<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'EDMS') }} — Enterprise Document Management</title>

    {{-- Tailwind v4 + Alpine.js + Chart.js (via Vite) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Livewire styles --}}
    @livewireStyles

    {{-- Bootstrap CSS (keeps existing inner-page components working) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    {{-- Trix rich text --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/trix/1.1.1/trix.css">
    {{-- Custom document CSS (legacy) --}}
    <link rel="stylesheet" href="{{ asset('custom-css/documents12.css') }}">

    {{-- SortableJS (needed in <head> so sidebar-enhancements.js finds it) --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    {{-- Dark mode FOUC prevention: apply class before first paint --}}
    <script>
        (function () {
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark-mode');
                document.addEventListener('DOMContentLoaded', function () {
                    document.body.classList.add('dark-mode');
                    var icon = document.getElementById('darkModeIcon');
                    if (icon) { icon.classList.replace('fa-moon', 'fa-sun'); }
                });
            }
        })();
    </script>
</head>

<body class="h-full" style="overflow:hidden">

@auth
    {{-- ── Loading Overlay ─────────────────────────── --}}
    <div id="loadingOverlay" style="display:none">
        <div class="spinner-border" role="status"></div>
    </div>

    {{-- ── File Preview Modal ───────────────────────── --}}
    @include('documents.previewDocument')

    {{-- ── Toast Container ─────────────────────────── --}}
    <div id="toast-container"></div>

    {{-- ── App Shell (Alpine-controlled) ──────────────────────────────── --}}
    <div
        class="edms-shell"
        id="appShell"
        x-data="{
            sidebarOpen: window.innerWidth >= 1024,
            init() {
                window.addEventListener('resize', () => {
                    this.sidebarOpen = window.innerWidth >= 1024;
                });
            }
        }"
        x-init="init()"
    >
        {{-- ── Sidebar ─────────────────────────────────────────────────── --}}
        <aside
            class="edms-sidebar"
            :class="{ 'collapsed': !sidebarOpen }"
            id="renderSidebarHtmlId"
        >
            @include('layouts.sidebar')
        </aside>

        {{-- ── Main Area ────────────────────────────────────────────────── --}}
        <div class="edms-main">

            {{-- ── Header ─────────────────────────────────────────────── --}}
            @if (!isset($shareDocument))
                @include('layouts.header')
            @endif

            {{-- ── Page Content (shown by JS after AJAX load) ──────────── --}}
            <div class="page-content" style="display:none">
                @if (!isset($shareDocument) && !Route::is('home'))
                    @include('layouts.navbar-search')
                @endif
                @yield('content')
            </div>

        </div>
    </div>

@else
    {{-- ── Unauthenticated layout ───────────────────── --}}
    <div style="min-height:100vh">
        <div class="page-content" style="display:block">
            @yield('content')
        </div>
    </div>
@endauth

{{-- ── Scripts ─────────────────────────────────────────────────────────── --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('custom-js/documents1001.js') }}"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
@livewireScripts

@auth
<script>
// ─── Dark mode toggle ──────────────────────────────────────────────────────
function edmsDarkModeToggle() {
    var isDark = document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', isDark);
    var icon = document.getElementById('darkModeIcon');
    if (icon) {
        icon.classList.toggle('fa-moon', !isDark);
        icon.classList.toggle('fa-sun',  isDark);
    }
}

// ─── Sidebar toggle — bridge Alpine.js ↔ existing JS ──────────────────────
document.addEventListener('alpine:init', () => {
    Alpine.effect(() => {
        const shell = document.getElementById('appShell');
        if (!shell) return;
        const alpineData = Alpine.$data(shell);
        if (!alpineData) return;
        // mirror old 'nav-closed' class for any legacy JS that checks it
        if (alpineData.sidebarOpen) {
            shell.classList.remove('nav-closed');
        } else {
            shell.classList.add('nav-closed');
        }
    });
});

// ─── Email helper ──────────────────────────────────────────────────────────
function sendEmail(formId) {
    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var formData  = new FormData($('#' + formId)[0]);
    formData.append('_token', csrfToken);
    formData.append('title', $('#documentTitle').val());
    formData.append('folder_id', localStorage.getItem('selectedFolderId'));
    formData.append('document_id', localStorage.getItem('selectedDocumentId'));
    $.ajax({
        url: '{{ route('send.email') }}', type: 'POST', data: formData,
        processData: false, contentType: false,
        headers: { 'X-CSRF-TOKEN': csrfToken },
        success: function(r) { $('#renderDocumentCommentHtml').html(r.html); edmsToast('Email sent successfully!', 'success'); },
        error: function()    { edmsToast('Failed to send email', 'error'); }
    });
}

// ─── File upload helpers ───────────────────────────────────────────────────
function uploadFiles() {
    const input = document.createElement('input');
    input.type = 'file'; input.multiple = true; input.style.display = 'none';
    input.addEventListener('change', function () {
        if (input.files.length > 0) uploadToServer(input.files, 'files');
    });
    document.body.appendChild(input); input.click();
}
function uploadFolder() {
    const input = document.createElement('input');
    input.type = 'file'; input.multiple = true;
    input.webkitdirectory = true; input.style.display = 'none';
    input.addEventListener('change', function () {
        if (input.files.length > 0) uploadToServer(input.files, 'folder');
    });
    document.body.appendChild(input); input.click();
}
function uploadToServer(files, type) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const formData  = new FormData();
    formData.append('_token', csrfToken);
    formData.append('folder_id', localStorage.getItem('selectedFolderId'));
    formData.append('document_id', localStorage.getItem('selectedDocumentId'));
    formData.append('type', type);
    for (let i = 0; i < files.length; i++) formData.append('files[]', files[i]);
    $.ajax({
        url: '/upload', type: 'POST', data: formData,
        processData: false, contentType: false,
        beforeSend: () => edmsToast('Uploading…', 'info', 0),
        success: function(r) {
            document.querySelectorAll('.toast-notif').forEach(t => t.remove());
            edmsToast('Upload complete!', 'success');
            fetchFiles(r.url, 'folder');
        },
        error: () => { document.querySelectorAll('.toast-notif').forEach(t => t.remove()); edmsToast('Upload failed', 'error'); }
    });
}

// ─── Subfolder toggle ──────────────────────────────────────────────────────
function toggleSubfolders(button) {
    var sf = button.nextElementSibling;
    sf.style.display = sf.style.display === 'none' ? 'block' : 'none';
    var folderId = button.parentNode.dataset.folderId;
    var sfOpen   = JSON.parse(localStorage.getItem('subfoldersOpen')) || {};
    sfOpen[folderId] = sf.style.display === 'block';
    localStorage.setItem('subfoldersOpen', JSON.stringify(sfOpen));
}
function selectSubfolder(id) { localStorage.setItem('selectedSubfolder', id); }

function previewDocumentImageFile(el) {
    var url = el.dataset.preview;
    var Ext = $('.previewFileExtension').val();
    previewCourseFile(Ext, url);
}

// ─── Modal helpers ─────────────────────────────────────────────────────────
function addUrlModal()    { $('#uploadModal').modal('show'); }
function closeOverlay()   { $('#previewModal').modal('hide'); }
function openModal(id) {
    var $m = $('#' + id);
    $m.find('.error-message').html('');
    $m.find('.invalid-feedback').remove();
    $m.find('.is-invalid').removeClass('is-invalid');
    var form = $m.find('#FolderCreateForm')[0];
    if (form) form.reset();
    $m.find('#renderFolderCategoryHtml').empty();
    $m.modal('show');
}
function closeModal(id) { $('#' + id).modal('hide'); $('.error-message').html(''); }

// ─── Validation helper ─────────────────────────────────────────────────────
function validation(xhr, $form) {
    if (xhr.status === 422) {
        var errors = xhr.responseJSON.errors;
        $form.find('.invalid-feedback').remove();
        $form.find('.is-invalid').removeClass('is-invalid');
        for (var key in errors) {
            var $input = $form.find('[name="' + key + '"]');
            $input.addClass('is-invalid');
            $form.find('.error-message').html('<div class="p-2 bg-danger text-white rounded">' + errors[key][0] + '</div>');
        }
    } else {
        $form.find('.error-message').html('<div class="p-2 bg-danger text-white rounded">' + xhr + '</div>');
    }
}
function saveForm(route, formId, cb) {
    var form = document.getElementById(formId);
    var $form = $('#' + formId);
    var data  = new FormData(form);
    $.ajax({
        url: route, type: 'POST', data: data, processData: false, contentType: false,
        success: function(r) { if (typeof cb === 'function') cb(r); },
        error:   function(xhr) { validation(xhr, $form); }
    });
}

// ─── Misc helpers ──────────────────────────────────────────────────────────
function copyUrl() {
    var el = document.getElementById('sharedUrlId');
    el.select(); el.setSelectionRange(0, 99999);
    document.execCommand('copy');
    el.blur();
    edmsToast('URL copied to clipboard!', 'success');
}
function generateRandomToken() {
    var ts = new Date().getTime().toString(16);
    return ts + '_' + Math.random().toString(36).substring(2, 10);
}
function showFilters() { $('.custom-dropdown').css('display', 'flex'); }
</script>
@endauth

</body>
</html>
