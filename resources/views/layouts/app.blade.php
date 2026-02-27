@php
    $theme = 'system';
    if (auth()->check()) {
        $theme = auth()->user()->theme ?? 'system';
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'OSTRICH') }} — Enterprise Document Management</title>

    {{-- Favicon — SVG (modern) + ICO (legacy fallback) --}}
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/img/fayiloli-icon.svg">

    {{-- Tailwind v4 + Alpine.js + Chart.js (via Vite) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Livewire styles --}}
    @livewireStyles

    {{-- TallStackUI styles & scripts --}}
    @tallStackUiStyle
    @tallStackUiScript

    {{-- Livewire scripts (forced in head for SPA) --}}
    @livewireScripts

    {{-- Global Scripts (Moved to head to prevent wire:navigate double-execution) --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    @auth
        <script src="{{ global_asset('custom-js/documents1001.js') }}"></script>
    @endauth

    {{-- Bootstrap CSS (keeps existing inner-page components working) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    {{-- Trix rich text --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/trix/1.1.1/trix.css">
    {{-- Custom document CSS (legacy) --}}
    <link rel="stylesheet" href="{{ global_asset('custom-css/documents12.css') }}">
    {{-- jQuery UI CSS (must be in

    <head> to avoid FOUC) --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">

        {{-- SortableJS (needed in

        <head> so sidebar-enhancements.js finds it) --}}
            <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

            <script>
                (function () {
                    var theme = '{{ $theme }}';
                    var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    var isDark = theme === 'dark' || (theme === 'system' && prefersDark);

                    if (isDark) {
                        document.documentElement.classList.add('dark', 'dark-mode');
                        document.addEventListener('DOMContentLoaded', function () {
                            document.body.classList.add('dark-mode');
                            var icon = document.getElementById('darkModeIcon');
                            if (icon) { icon.classList.replace('fa-moon', 'fa-sun'); }
                        });
                    }
                })();
            </script>

            {{-- ── Tenant Custom Branding Injection ── --}}
            @php
                $currentTenant = tenancy()->initialized ? tenancy()->tenant : null;
                $primaryColor = $currentTenant->settings['brand_color'] ?? null;
            @endphp
            @if($primaryColor)
                <style>
                    :root {
                        /* Applying customized tenant branding overriding default Violet */
                        --color-primary-500:
                            {{ $primaryColor }}
                        ;
                        --color-primary-600:
                            {{ $primaryColor }}
                        ;
                        /* Dynamic focus rings reflecting brand color */
                        --tw-ring-color:
                            {{ $primaryColor }}
                        ;
                    }
                </style>
            @endif
        </head>

    <body
        class="min-h-screen bg-slate-100 text-slate-800 dark:bg-slate-950 dark:text-slate-200 font-sans antialiased text-base leading-7 transition-colors duration-200 ease-in-out @auth overflow-hidden @endauth">

        @auth
            {{-- ── Loading Overlay ─────────────────────────── --}}
            <div id="loadingOverlay" style="display:none">
                <div class="spinner-border" role="status"></div>
            </div>

            {{-- ── File Preview Modal ───────────────────────── --}}
            @include('documents.previewDocument')

            {{-- ── TALLStackUI Interactions ─────────────────── --}}
            <x-ts-toast />
            <x-ts-dialog />

            {{-- ── App Shell (Alpine & Tailwind) ──────────────────────────────── --}}
            <div x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' }"
                @keydown.window.escape="sidebarOpen = false"
                class="flex h-screen bg-slate-50 dark:bg-slate-900 overflow-hidden">

                {{-- ── Mobile Sidebar Backdrop ─────────────────────────────────── --}}
                <div x-show="sidebarOpen" class="relative z-50 lg:hidden"
                    x-description="Off-canvas menu for mobile, show/hide based on off-canvas menu state." role="dialog"
                    aria-modal="true">

                    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="transition-opacity ease-linear duration-300"
                        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                        class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="sidebarOpen = false"
                        aria-hidden="true"></div>

                    <div class="fixed inset-0 flex">
                        <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform"
                            x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                            x-transition:leave="transition ease-in-out duration-300 transform"
                            x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                            class="relative mr-16 flex w-full max-w-xs flex-1">

                            <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                                <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                                    <span class="sr-only">Close sidebar</span>
                                    <i class="fas fa-times text-white text-xl" aria-hidden="true"></i>
                                </button>
                            </div>

                            {{-- Mobile Sidebar Content --}}
                            <div
                                class="flex grow flex-col gap-y-5 overflow-y-auto bg-white dark:bg-slate-800 px-6 pb-4 border-r border-slate-200 dark:border-slate-700 shadow-xl">
                                @include('layouts.sidebar')
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Desktop Sidebar ─────────────────────────────────────────── --}}
                <aside
                    class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:flex-col transition-all duration-300 ease-in-out"
                    :class="sidebarCollapsed ? 'lg:w-18' : 'lg:w-72'" id="renderSidebarHtmlId">
                    <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white dark:bg-slate-800 pb-4 border-r border-slate-200 dark:border-slate-700 shadow-sm transition-all duration-300"
                        :class="sidebarCollapsed ? 'px-2' : 'px-6'">
                        @include('layouts.sidebar')
                    </div>
                </aside>

                {{-- ── Main Area ────────────────────────────────────────────────── --}}
                <div class="flex flex-1 flex-col h-full w-full transition-all duration-300 ease-in-out"
                    :class="sidebarCollapsed ? 'lg:pl-18' : 'lg:pl-72'">

                    {{-- ── Header ─────────────────────────────────────────────── --}}
                    @if (!isset($shareDocument))
                        @include('layouts.header')
                    @endif

                    <main class="flex-1 overflow-x-hidden overflow-y-auto w-full page-content focus:outline-none"
                        style="{{ Route::is('documents.index') ? 'display:none' : 'display:block' }}">
                        <div class="w-full mx-auto p-4 sm:p-6 lg:p-8">
                            @if (!isset($shareDocument) && !Route::is('home') && !Route::is('documents.index'))
                                @include('layouts.navbar-search')
                            @endif
                            @yield('content')
                        </div>
                    </main>

                </div>
            </div>

        @else
            {{--
            Unauthenticated layout — auth shell owns its own min-height and layout.
            Do NOT wrap in .page-content: that class carries EDMS-app CSS
            (background:#f8fafc, overflow-x:hidden, flex:1) that conflicts with
            the full-bleed auth-shell design.
            --}}
            @yield('content')
        @endauth

        @auth
            <script>
                // ─── Dark mode toggle ──────────────────────────────────────────────────────
                function edmsDarkModeToggle() {
                    var isDark = document.body.classList.toggle('dark-mode');
                    document.documentElement.classList.toggle('dark', isDark);

                    localStorage.setItem('darkMode', isDark);
                    var icon = document.getElementById('darkModeIcon');
                    if (icon) {
                        icon.classList.toggle('fa-moon', !isDark);
                        icon.classList.toggle('fa-sun', isDark);
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
                    var formData = new FormData($('#' + formId)[0]);
                    formData.append('_token', csrfToken);
                    formData.append('title', $('#documentTitle').val());
                    formData.append('folder_id', localStorage.getItem('selectedFolderId'));
                    formData.append('document_id', localStorage.getItem('selectedDocumentId'));
                    $.ajax({
                        url: '{{ route('send.email') }}', type: 'POST', data: formData,
                        processData: false, contentType: false,
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        success: function (r) { $('#renderDocumentCommentHtml').html(r.html); edmsToast('Email sent successfully!', 'success'); },
                        error: function () { edmsToast('Failed to send email', 'error'); }
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
                    const formData = new FormData();
                    formData.append('_token', csrfToken);
                    formData.append('folder_id', localStorage.getItem('selectedFolderId'));
                    formData.append('document_id', localStorage.getItem('selectedDocumentId'));
                    formData.append('type', type);
                    for (let i = 0; i < files.length; i++) formData.append('files[]', files[i]);
                    $.ajax({
                        url: '/upload', type: 'POST', data: formData,
                        processData: false, contentType: false,
                        beforeSend: () => edmsToast('Uploading…', 'info', 0),
                        success: function (r) {
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
                    var sfOpen = JSON.parse(localStorage.getItem('subfoldersOpen')) || {};
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
                function addUrlModal() { $('#uploadModal').modal('show'); }
                function closeOverlay() { $('#previewModal').modal('hide'); }
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
                    var data = new FormData(form);
                    $.ajax({
                        url: route, type: 'POST', data: data, processData: false, contentType: false,
                        success: function (r) { if (typeof cb === 'function') cb(r); },
                        error: function (xhr) { validation(xhr, $form); }
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

            {{-- ── Server-side flash → edmsToast bridge ────────────────────────────── --}}
            {{-- Converts redirect()->with('success'/'error'/'warning'/'info') into the --}}
            {{-- same toast UX used by AJAX responses, so both paths look identical. --}}
            @if(session()->hasAny(['success', 'error', 'warning', 'info']))
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        if (typeof edmsToast !== 'function') return;
                                                                                                            @if(session('success')) edmsToast(@json(session('success')), 'success'); @endif
                        @if(session('error'))   edmsToast(@json(session('error')), 'error'); @endif
                        @if(session('warning')) edmsToast(@json(session('warning')), 'warning'); @endif
                        @if(session('info'))    edmsToast(@json(session('info')), 'info'); @endif
                                                                                                        });
                </script>
            @endif
        @endauth

    </body>

</html>