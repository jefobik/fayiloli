@extends('layouts.app')

@section('content')
    {{--
    Two-pane enterprise document browser.
    Negative margins cancel the p-4/p-6/p-8 wrapper from app.blade.php so the
    browser fills the full main area. Height is bounded to viewport-minus-header.
    All div IDs required by documents1001.js are preserved:
    #documentContent, #renderDocumentContentHtml, #documentProperty,
    #documentCommentSection, #renderDocumentCommentHtml
    --}}
    <div class="edms-browser flex flex-1 min-h-0 flex-col overflow-hidden bg-[var(--color-surface-muted)] dark:bg-[var(--color-surface-muted-dark)] w-full h-full"
        x-data="{
                     viewMode: localStorage.getItem('docViewMode') || 'grid',
                     detailVisible: false,
                     isDragging: false
                 }" x-init="
                     $watch('viewMode', function(val) {
                         localStorage.setItem('docViewMode', val);
                         var c = document.getElementById('documentContent');
                         if (c) c.setAttribute('data-view', val);
                     });
                     var c = document.getElementById('documentContent');
                     if (c) c.setAttribute('data-view', viewMode);
                 " @view-mode-updated.window="viewMode = $event.detail.mode" @dragover.window.prevent="isDragging = true"
        @dragleave.window="isDragging = false"
        @drop.window.prevent="isDragging = false; if(typeof uploadToServer === 'function') uploadToServer($event.dataTransfer.files, 'files')">

        {{-- ── Drop zone overlay ──────────────────────────────────────────────── --}}
        <div x-show="isDragging" x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[200] pointer-events-none flex items-center justify-center" aria-hidden="true"
            style="display:none">
            <div
                class="absolute inset-0 bg-indigo-600/10 backdrop-blur-sm border-4 border-dashed border-indigo-400 dark:border-indigo-500 rounded-none">
            </div>
            <div
                class="relative bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-2xl px-10 py-8 shadow-2xl text-center border border-[var(--color-primary)]/20 dark:border-[var(--color-primary)]/30">
                <div
                    class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-indigo-50 dark:bg-indigo-900/40 text-indigo-500">
                    <i class="fas fa-cloud-upload-alt text-3xl" aria-hidden="true"></i>
                </div>
                <p class="text-base font-extrabold text-slate-900 dark:text-white">Drop files to upload</p>
                <p class="text-xs text-slate-500 mt-1">Files will be added to the current workspace</p>
            </div>
        </div>

        {{-- ── COMMAND BAR ────────────────────────────────────────────────────── --}}
        <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap px-3 sm:px-4 py-2 border-b
                            border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] shrink-0
                            shadow-sm" role="toolbar" aria-label="Document actions">

            @can('create documents')
                {{-- Primary: Upload Files --}}
                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold
                                               bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800
                                               text-white rounded-lg shadow-sm transition-colors
                                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                    onclick="uploadFiles()" aria-label="Upload files">
                    <i class="fas fa-arrow-up-tray" aria-hidden="true"></i>
                    <span class="hidden sm:inline">Upload Files</span>
                </button>

                {{-- Upload Folder --}}
                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold
                                               text-[var(--color-text-main)] dark:text-[var(--color-text-main-dark)]
                                               bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)]
                                               border border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)] rounded-lg
                                               hover:bg-[var(--color-surface-hover)] dark:hover:bg-[var(--color-surface-hover-dark)] transition-colors
                                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                    onclick="openModal('uploadFolderModal')" aria-label="Upload folder">
                    <i class="fas fa-folder-open text-[var(--color-text-muted)] dark:text-[var(--color-text-muted-dark)]"
                        aria-hidden="true"></i>
                    <span class="hidden sm:inline">Folder</span>
                </button>

                {{-- Add URL --}}
                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold
                                               text-[var(--color-text-main)] dark:text-[var(--color-text-main-dark)]
                                               bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)]
                                               border border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)] rounded-lg
                                               hover:bg-[var(--color-surface-hover)] dark:hover:bg-[var(--color-surface-hover-dark)] transition-colors
                                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                    onclick="openModal('uploadModal')" aria-label="Add URL document">
                    <i class="fas fa-link text-[var(--color-text-muted)] dark:text-[var(--color-text-muted-dark)]"
                        aria-hidden="true"></i>
                    <span class="hidden sm:inline">Add URL</span>
                </button>
            @endcan

            <div class="hidden sm:block h-5 w-px bg-[var(--color-border-subtle)] dark:bg-[var(--color-border-subtle-dark)] mx-0.5"
                role="separator" aria-hidden="true">
            </div>

            {{-- Request --}}
            <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold
                                   text-[var(--color-text-main)] dark:text-[var(--color-text-main-dark)]
                                   bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)]
                                   border border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)] rounded-lg
                                   hover:bg-[var(--color-surface-hover)] dark:hover:bg-[var(--color-surface-hover-dark)] transition-colors
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                data-bs-toggle="modal" data-bs-target="#requestDocumentModal" onclick="requestDocument()"
                aria-label="Request a document" aria-haspopup="dialog">
                <i class="fas fa-arrow-down-tray text-[var(--color-text-muted)] dark:text-[var(--color-text-muted-dark)]"
                    aria-hidden="true"></i>
                <span class="hidden md:inline">Request</span>
            </button>

            @can('share documents')
                {{-- Share --}}
                <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold
                                               text-[var(--color-text-main)] dark:text-[var(--color-text-main-dark)]
                                               bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)]
                                               border border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)] rounded-lg
                                               hover:bg-[var(--color-surface-hover)] dark:hover:bg-[var(--color-surface-hover-dark)] transition-colors
                                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                    data-bs-toggle="modal" data-bs-target="#shareDocumentModal" onclick="shareDocument()"
                    aria-label="Share document" aria-haspopup="dialog">
                    <i class="fas fa-share-alt text-[var(--color-text-muted)] dark:text-[var(--color-text-muted-dark)]"
                        aria-hidden="true"></i>
                    <span class="hidden md:inline">Share</span>
                </button>
            @endcan

            {{-- Spacer --}}
            <div class="flex-1 min-w-0" aria-hidden="true"></div>

            {{-- View toggle ──────────────────────────────────────── --}}
            <livewire:documents.view-toggle />

            {{-- Detail panel toggle (mobile) --}}
            <button type="button" class="lg:hidden w-8 h-8 flex items-center justify-center rounded-lg border
                                   border-slate-200 dark:border-slate-700 text-slate-400
                                   hover:text-indigo-600 dark:hover:text-indigo-400
                                   hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500"
                @click="detailVisible = !detailVisible" :aria-pressed="detailVisible.toString()"
                aria-label="Toggle properties panel">
                <i class="fas fa-sidebar text-[0.7rem]" aria-hidden="true"></i>
            </button>

        </div>{{-- /COMMAND BAR --}}

        {{-- ── MAIN CONTENT + DETAIL PANEL ───────────────────────────────────── --}}
        <div class="flex flex-1 overflow-hidden">

            {{-- Document grid / list (AJAX target preserved) ─────────────────── --}}
            <div id="documentContent"
                class="flex-1 overflow-y-auto p-3 sm:p-4 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-700"
                data-view="grid">
                <div id="renderDocumentContentHtml">
                    @if ($documents->isEmpty())
                        <x-notFound icon="far fa-folder-open" title="No Documents Here"
                            message="Select a workspace from the sidebar, or upload files to get started." />
                    @endif
                </div>
            </div>

            {{-- Detail / properties panel ────────────────────────────────────── --}}
            <aside id="documentProperty" class="w-72 xl:w-80 shrink-0 border-l border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)]
                                  overflow-y-auto bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)]
                                  transition-all duration-300
                                  hidden lg:block" :class="detailVisible ? '!block' : ''" aria-label="Document properties">

                {{-- Info panel (JS populates .fileProperty, .folderProperty, etc.) --}}
                @include('documents.info')

                {{-- Comment section (shown by JS on addComment()) --}}
                <div id="documentCommentSection"
                    class="border-t border-[var(--color-border-subtle)] dark:border-[var(--color-border-subtle-dark)]"
                    style="display: none">
                    <div id="renderDocumentCommentHtml"></div>
                </div>

            </aside>

        </div>{{-- /MAIN CONTENT + DETAIL PANEL --}}

    </div>{{-- /edms-browser --}}


    {{-- ── Upload / Request / Share modals (unchanged) ────────────────────────── --}}
    @include('documents.uploads.addUrl')
    @include('documents.uploads.uploadFolder')
    @include('documents.uploads.requestDocument')
    @include('documents.uploads.shareDocument')


    {{-- ── Detail panel modern overrides ─────────────────────────────────────── --}}
    <style>
        /* Prevent the legacy dark-panel CSS from clashing with the sidebar */
        #documentProperty .document-properties {
            height: auto;
            width: 100%;
        }

        #documentProperty .document-properties .card {
            background-color: transparent;
            border: none;
            border-radius: 0;
            height: auto;
            box-shadow: none;
        }

        #documentProperty .document-properties .card-header {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            border-radius: 0;
            padding: 1rem;
            text-align: center;
        }

        .dark-mode #documentProperty .document-properties .card-header {
            background-color: #0f172a;
            border-bottom-color: #1e293b;
        }

        #documentProperty .document-properties .card-header img.defaultImage {
            width: 72px;
            height: 72px;
            display: block;
            margin: 0 auto;
            object-fit: contain;
            filter: none;
            opacity: 0.85;
        }

        #documentProperty .document-properties .card-header-img img {
            width: 100%;
            height: 140px;
            object-fit: cover;
        }

        #documentProperty .document-properties .card-header .icon {
            background-color: #6366f1;
            font-size: 0.75rem;
            padding: 4px 6px;
        }

        #documentProperty .document-properties .card-body {
            background-color: transparent;
            padding: 1rem;
        }

        #documentProperty .document-properties .card-body.fileProperty,
        #documentProperty .document-properties .card-body.folderProperty {
            background-color: transparent;
        }

        #documentProperty .document-properties label {
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .dark-mode #documentProperty .document-properties label {
            color: #94a3b8;
        }

        #documentProperty .document-properties input,
        #documentProperty .document-properties select {
            background-color: #f8fafc;
            color: #0f172a;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            height: 32px;
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }

        .dark-mode #documentProperty .document-properties input,
        .dark-mode #documentProperty .document-properties select {
            background-color: #1e293b;
            color: #f1f5f9;
            border-color: #334155;
        }

        #documentProperty .document-properties input:focus,
        #documentProperty .document-properties select:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.4);
            border-color: #6366f1;
            background-color: #fff;
            color: #0f172a;
        }

        .dark-mode #documentProperty .document-properties input:focus,
        .dark-mode #documentProperty .document-properties select:focus {
            background-color: #1e293b;
            color: #f1f5f9;
        }

        #documentProperty .share-buttons>i {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            color: #475569;
            font-size: 0.85rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s;
        }

        #documentProperty .share-buttons>i:hover {
            background-color: #6366f1;
            border-color: #6366f1;
            color: #fff;
        }

        .dark-mode #documentProperty .share-buttons>i {
            border-color: #334155;
            color: #94a3b8;
        }

        /* Scrollbar for content area */
        #documentContent::-webkit-scrollbar {
            width: 6px;
        }

        #documentContent::-webkit-scrollbar-track {
            background: transparent;
        }

        #documentContent::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 3px;
        }

        .dark-mode #documentContent::-webkit-scrollbar-thumb {
            background-color: #334155;
        }
    </style>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var content = document.querySelector('.page-content');
            if (content) content.style.display = 'block';
            var overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.style.display = 'none';
        });
    </script>
@endsection