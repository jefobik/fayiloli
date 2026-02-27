@extends('layouts.app')
@section('content')
    <div class="container py-4"
         x-data="{ density: localStorage.getItem('tagsTableDensity') || 'relaxed' }"
         x-init="$watch('density', val => localStorage.setItem('tagsTableDensity', val))">

        {{-- ── Header ──────────────────────────────────────────────────────────── --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h1 class="h4 fw-bold text-primary mb-0">
                    <i class="fas fa-tags me-2" aria-hidden="true"></i>Tags
                </h1>
                <p class="text-muted small mb-0">Manage classification tags and their colour codes</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button"
                        @click="density = density === 'relaxed' ? 'compact' : 'relaxed'"
                        class="btn btn-outline-secondary btn-sm"
                        aria-label="Toggle Table Density" title="Toggle Table Density">
                    <i class="fas"
                       :class="density === 'relaxed' ? 'fa-compress-arrows-alt' : 'fa-expand-arrows-alt'"
                       aria-hidden="true"></i>
                </button>
                <button type="button" class="btn btn-success btn-sm"
                        onclick="openAddTagModal()"
                        aria-label="Create new tag">
                    <i class="fas fa-plus me-1" aria-hidden="true"></i> New Tag
                </button>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert" aria-live="assertive">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ── Tags Table ───────────────────────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-0">
                <div class="table-responsive overflow-y-auto max-h-[65vh] rounded-b-lg
                            scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                    <table class="table table-hover align-middle mb-0 w-full" aria-label="Tags list">
                        <thead class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-800 shadow-sm border-b
                                     border-slate-200 dark:border-slate-700 transition-all duration-200">
                            <tr>
                                <th scope="col"
                                    class="ps-4 text-xs font-semibold text-slate-500 uppercase tracking-wider transition-all duration-200"
                                    :class="density === 'compact' ? 'py-2' : 'py-3'">Name</th>
                                <th scope="col"
                                    class="text-xs font-semibold text-slate-500 uppercase tracking-wider transition-all duration-200"
                                    :class="density === 'compact' ? 'py-2' : 'py-3'">Code</th>
                                <th scope="col"
                                    class="text-xs font-semibold text-slate-500 uppercase tracking-wider transition-all duration-200"
                                    :class="density === 'compact' ? 'py-2' : 'py-3'">Colours</th>
                                <th scope="col"
                                    class="text-xs font-semibold text-slate-500 uppercase tracking-wider transition-all duration-200"
                                    :class="density === 'compact' ? 'py-2' : 'py-3'">Preview</th>
                                <th scope="col"
                                    class="text-end pe-4 text-xs font-semibold text-slate-500 uppercase tracking-wider transition-all duration-200"
                                    :class="density === 'compact' ? 'py-2' : 'py-3'">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tags as $tag)
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/50
                                           transition-colors border-b border-slate-100 dark:border-slate-800 last:border-0">

                                    {{-- Name --}}
                                    <td class="ps-4 fw-semibold transition-all duration-200"
                                        :class="density === 'compact' ? 'py-1' : 'py-2'" data-label="Name">
                                        {{ $tag->name }}
                                    </td>

                                    {{-- Code --}}
                                    <td class="transition-all duration-200" :class="density === 'compact' ? 'py-1' : 'py-2'"
                                        data-label="Code">
                                        @if ($tag->code)
                                            <code class="text-secondary" style="font-size:0.8rem">{{ $tag->code }}</code>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>

                                    {{-- Colours --}}
                                    <td class="transition-all duration-200" :class="density === 'compact' ? 'py-1' : 'py-2'"
                                        data-label="Colours">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            @if ($tag->background_color)
                                                <span class="d-flex align-items-center gap-1">
                                                    <span class="d-inline-block border rounded"
                                                          style="width:16px;height:16px;background:{{ $tag->background_color }}"
                                                          title="Background: {{ $tag->background_color }}"
                                                          aria-label="Background colour {{ $tag->background_color }}"></span>
                                                    <code class="text-secondary" style="font-size:0.72rem">{{ $tag->background_color }}</code>
                                                </span>
                                            @endif
                                            @if ($tag->foreground_color)
                                                <span class="d-flex align-items-center gap-1">
                                                    <span class="d-inline-block border rounded"
                                                          style="width:16px;height:16px;background:{{ $tag->foreground_color }}"
                                                          title="Text: {{ $tag->foreground_color }}"
                                                          aria-label="Foreground colour {{ $tag->foreground_color }}"></span>
                                                    <code class="text-secondary" style="font-size:0.72rem">{{ $tag->foreground_color }}</code>
                                                </span>
                                            @endif
                                            @if (!$tag->background_color && !$tag->foreground_color)
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Preview --}}
                                    <td class="transition-all duration-200" :class="density === 'compact' ? 'py-1' : 'py-2'"
                                        data-label="Preview">
                                        <span class="badge rounded-pill"
                                              style="background-color:{{ $tag->background_color ?? '#6c757d' }};
                                                     color:{{ $tag->foreground_color ?? '#ffffff' }};
                                                     font-size:0.75rem;padding:0.3em 0.7em">
                                            {{ $tag->name }}
                                        </span>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="text-end pe-4 transition-all duration-200"
                                        :class="density === 'compact' ? 'py-1' : 'py-2'" data-label="Actions">
                                        <div class="d-flex justify-content-end gap-1
                                                    opacity-100 lg:opacity-0
                                                    group-hover:opacity-100 focus-within:opacity-100
                                                    transition-opacity duration-200"
                                             role="group" aria-label="Actions for tag {{ $tag->name }}">

                                            {{-- Edit --}}
                                            <button type="button"
                                                    class="btn btn-outline-primary"
                                                    :class="density === 'compact' ? 'btn-sm py-0' : 'btn-sm'"
                                                    aria-label="Edit tag {{ $tag->name }}"
                                                    data-tag-id="{{ $tag->id }}"
                                                    data-tag-name="{{ $tag->name }}"
                                                    data-tag-code="{{ $tag->code }}"
                                                    data-tag-bg="{{ $tag->background_color }}"
                                                    data-tag-fg="{{ $tag->foreground_color }}"
                                                    data-update-url="{{ route('tags.update', $tag) }}"
                                                    onclick="editTag(this)">
                                                <i class="fas fa-pen" aria-hidden="true"
                                                   :class="density === 'compact' ? 'small' : ''"></i>
                                            </button>

                                            {{-- Delete --}}
                                            <button type="button"
                                                    class="btn btn-outline-danger"
                                                    :class="density === 'compact' ? 'btn-sm py-0' : 'btn-sm'"
                                                    aria-label="Delete tag {{ $tag->name }}"
                                                    data-tag-name="{{ $tag->name }}"
                                                    data-delete-url="{{ route('tags.destroy', $tag) }}"
                                                    onclick="confirmDeleteTag(this)">
                                                <i class="fas fa-trash-alt" aria-hidden="true"
                                                   :class="density === 'compact' ? 'small' : ''"></i>
                                            </button>
                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="fas fa-tags fa-2x mb-2 d-block" aria-hidden="true"></i>
                                        No tags yet.
                                        <button type="button" class="btn btn-link p-0 align-baseline"
                                                onclick="openAddTagModal()">
                                            Create the first tag.
                                        </button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($tags->hasPages())
                <div class="card-footer bg-transparent border-0">
                    {{ $tags->links() }}
                </div>
            @endif
        </div>

        {{-- ── Tag Assignments (folder → category → tag map) ─────────────────────── --}}
        @if ($folders->isNotEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-sitemap me-2 text-secondary" aria-hidden="true"></i>
                        Tag Assignments
                    </h6>
                    <span class="badge bg-light text-secondary border">
                        {{ $folders->count() }} workspace{{ $folders->count() !== 1 ? 's' : '' }}
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive overflow-y-auto max-h-[65vh] rounded-b-lg
                                scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                        <table class="table table-hover align-middle mb-0 w-full"
                               aria-label="Tag assignments by folder">
                            <thead class="sticky top-0 z-10 bg-slate-50 dark:bg-slate-800 shadow-sm border-b
                                         border-slate-200 dark:border-slate-700 transition-all duration-200">
                                <tr>
                                    <th scope="col"
                                        class="ps-4 text-xs font-semibold text-slate-500 uppercase tracking-wider transition-all duration-200"
                                        :class="density === 'compact' ? 'py-2' : 'py-3'">Workspace / Folder</th>
                                    <th scope="col"
                                        class="text-xs font-semibold text-slate-500 uppercase tracking-wider transition-all duration-200"
                                        :class="density === 'compact' ? 'py-2' : 'py-3'">Category</th>
                                    <th scope="col"
                                        class="text-xs font-semibold text-slate-500 uppercase tracking-wider transition-all duration-200"
                                        :class="density === 'compact' ? 'py-2' : 'py-3'">Tags</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($folders as $folder)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50
                                               transition-colors border-b border-slate-100 dark:border-slate-800 last:border-0">
                                        <td class="ps-4 fw-semibold transition-all duration-200"
                                            :class="density === 'compact' ? 'py-1' : 'py-2'" data-label="Folder">
                                            {{ $folder->name }}
                                            @foreach ($folder->subfolders as $sub)
                                                <span class="text-muted fw-normal"> / {{ $sub->name }}</span>
                                            @endforeach
                                        </td>
                                        <td class="transition-all duration-200"
                                            :class="density === 'compact' ? 'py-1' : 'py-2'" data-label="Category">
                                            @forelse ($folder->categories as $category)
                                                <span class="badge bg-light text-secondary border me-1">{{ $category->name }}</span>
                                            @empty
                                                <span class="text-muted small">—</span>
                                            @endforelse
                                        </td>
                                        <td class="transition-all duration-200"
                                            :class="density === 'compact' ? 'py-1' : 'py-2'" data-label="Tags">
                                            @php $hasTags = false; @endphp
                                            @foreach ($folder->categories as $category)
                                                @foreach ($category->tags as $tag)
                                                    @php $hasTags = true; @endphp
                                                    <span class="badge me-1"
                                                          style="background-color:{{ $tag->background_color ?? '#6c757d' }};
                                                                 color:{{ $tag->foreground_color ?? '#fff' }}">
                                                        {{ $tag->name }}
                                                    </span>
                                                @endforeach
                                            @endforeach
                                            @if (!$hasTags)
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

    </div>{{-- /container --}}


    {{-- ══════════════════════════════════════════════════════════════════════════
         CREATE / EDIT TAG MODAL
    ════════════════════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="addTagModal" tabindex="-1" aria-labelledby="addTagModalLabel"
         aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="addTagModalLabel">
                        <i class="fas fa-tag me-2 text-indigo-500" aria-hidden="true"></i>
                        <span id="tagModalTitle">New Tag</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close tag modal"></button>
                </div>

                <div class="modal-body">
                    <div id="tagFormError" class="alert alert-danger py-2 small d-none" role="alert"
                         aria-live="assertive"></div>

                    <form id="tagForm" novalidate>
                        @csrf
                        {{-- Hidden _method field (toggled between POST/PUT by editTag()) --}}
                        <input type="hidden" name="_method" id="tagFormMethod" value="POST">

                        {{-- Name --}}
                        <div class="mb-3">
                            <label for="tagName" class="form-label fw-semibold small text-slate-700">
                                Tag Name <span class="text-danger" aria-hidden="true">*</span>
                            </label>
                            <input type="text" class="form-control form-control-sm" id="tagName"
                                   name="name" placeholder="e.g. Urgent" required
                                   aria-required="true" autocomplete="off">
                            <div class="invalid-feedback">Tag name is required.</div>
                        </div>

                        {{-- Code --}}
                        <div class="mb-3">
                            <label for="tagCode" class="form-label fw-semibold small text-slate-700">
                                Code <span class="text-muted fw-normal">(optional identifier)</span>
                            </label>
                            <input type="text" class="form-control form-control-sm" id="tagCode"
                                   name="code" placeholder="e.g. URG" autocomplete="off">
                        </div>

                        {{-- Colors --}}
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label for="tagBg" class="form-label fw-semibold small text-slate-700">
                                    Background Colour
                                </label>
                                <div class="input-group input-group-sm">
                                    <input type="color" class="form-control form-control-color p-1"
                                           id="tagBgColor" name="background_color" value="#6366f1"
                                           title="Choose background colour" aria-label="Background colour">
                                    <input type="text" class="form-control form-control-sm" id="tagBg"
                                           name="_bg_text" placeholder="#6366f1" maxlength="7"
                                           aria-label="Background colour hex code">
                                </div>
                            </div>
                            <div class="col-6">
                                <label for="tagFg" class="form-label fw-semibold small text-slate-700">
                                    Text Colour
                                </label>
                                <div class="input-group input-group-sm">
                                    <input type="color" class="form-control form-control-color p-1"
                                           id="tagFgColor" name="foreground_color" value="#ffffff"
                                           title="Choose text colour" aria-label="Text colour">
                                    <input type="text" class="form-control form-control-sm" id="tagFg"
                                           name="_fg_text" placeholder="#ffffff" maxlength="7"
                                           aria-label="Text colour hex code">
                                </div>
                            </div>
                        </div>

                        {{-- Live preview --}}
                        <div class="mb-1">
                            <p class="form-label fw-semibold small text-slate-700 mb-1">Preview</p>
                            <span id="tagPreview"
                                  class="badge rounded-pill"
                                  style="background-color:#6366f1;color:#ffffff;font-size:0.85rem;padding:0.35em 0.85em">
                                Tag Name
                            </span>
                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-primary" id="tagSaveBtn"
                            onclick="saveTag()">
                        <i class="fas fa-check me-1" aria-hidden="true"></i>
                        Save Tag
                    </button>
                </div>

            </div>
        </div>
    </div>


    {{-- ── Delete Confirmation Modal ───────────────────────────────────────── --}}
    <div class="modal fade" id="deleteTagModal" tabindex="-1" aria-labelledby="deleteTagModalLabel"
         aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-danger fw-semibold" id="deleteTagModalLabel">
                        <i class="fas fa-triangle-exclamation me-2" aria-hidden="true"></i>Delete Tag
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Cancel deletion"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="mb-0 small">
                        Delete tag <strong id="deleteTagName"></strong>?
                        This action cannot be undone.
                    </p>
                </div>
                <div class="modal-footer border-0 pt-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteTagForm" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    {{-- ── Also include category-tags modal (for folder create flow) ─────── --}}
    @include('folders.modals.addTag')


    <script>
        /* ── Tag modal: open for CREATE ──────────────────────────────────────── */
        function openAddTagModal() {
            // Reset form to create mode
            document.getElementById('tagModalTitle').textContent = 'New Tag';
            document.getElementById('tagFormMethod').value = 'POST';
            document.getElementById('tagSaveBtn').dataset.updateUrl = '';
            document.getElementById('tagForm').reset();
            document.getElementById('tagBgColor').value = '#6366f1';
            document.getElementById('tagFgColor').value = '#ffffff';
            document.getElementById('tagBg').value = '#6366f1';
            document.getElementById('tagFg').value = '#ffffff';
            updateTagPreview();
            document.getElementById('tagFormError').classList.add('d-none');
            new bootstrap.Modal(document.getElementById('addTagModal')).show();
        }

        /* ── Tag modal: open for EDIT ────────────────────────────────────────── */
        function editTag(btn) {
            var name  = btn.getAttribute('data-tag-name')  || '';
            var code  = btn.getAttribute('data-tag-code')  || '';
            var bg    = btn.getAttribute('data-tag-bg')    || '#6366f1';
            var fg    = btn.getAttribute('data-tag-fg')    || '#ffffff';
            var url   = btn.getAttribute('data-update-url');

            document.getElementById('tagModalTitle').textContent = 'Edit Tag';
            document.getElementById('tagFormMethod').value = 'PUT';
            document.getElementById('tagSaveBtn').dataset.updateUrl = url;
            document.getElementById('tagName').value    = name;
            document.getElementById('tagCode').value    = code;
            document.getElementById('tagBgColor').value = bg || '#6366f1';
            document.getElementById('tagFgColor').value = fg || '#ffffff';
            document.getElementById('tagBg').value      = bg || '#6366f1';
            document.getElementById('tagFg').value      = fg || '#ffffff';
            updateTagPreview();
            document.getElementById('tagFormError').classList.add('d-none');
            new bootstrap.Modal(document.getElementById('addTagModal')).show();
        }

        /* ── Tag modal: save (create or update) ──────────────────────────────── */
        function saveTag() {
            var nameEl  = document.getElementById('tagName');
            var name    = nameEl.value.trim();
            if (!name) {
                nameEl.classList.add('is-invalid');
                nameEl.focus();
                return;
            }
            nameEl.classList.remove('is-invalid');

            var method    = document.getElementById('tagFormMethod').value;
            var updateUrl = document.getElementById('tagSaveBtn').dataset.updateUrl;
            var url       = (method === 'PUT' && updateUrl) ? updateUrl : '{{ route('tags.store') }}';

            var formData  = new FormData();
            formData.append('_token',           '{{ csrf_token() }}');
            formData.append('_method',          method);
            formData.append('name',             name);
            formData.append('code',             document.getElementById('tagCode').value.trim());
            formData.append('background_color', document.getElementById('tagBgColor').value);
            formData.append('foreground_color', document.getElementById('tagFgColor').value);

            var saveBtn = document.getElementById('tagSaveBtn');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1" aria-hidden="true"></i>Saving…';

            fetch(url, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) {
                    if (r.redirected || r.ok) {
                        bootstrap.Modal.getInstance(document.getElementById('addTagModal')).hide();
                        window.location.reload();
                    } else {
                        return r.json().then(function (data) {
                            var errEl  = document.getElementById('tagFormError');
                            var errMsg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Failed to save tag.');
                            errEl.textContent = errMsg;
                            errEl.classList.remove('d-none');
                        });
                    }
                })
                .catch(function () {
                    document.getElementById('tagFormError').textContent = 'Network error. Please try again.';
                    document.getElementById('tagFormError').classList.remove('d-none');
                })
                .finally(function () {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fas fa-check me-1" aria-hidden="true"></i>Save Tag';
                });
        }

        /* ── Delete confirmation ─────────────────────────────────────────────── */
        function confirmDeleteTag(btn) {
            document.getElementById('deleteTagName').textContent = btn.getAttribute('data-tag-name');
            document.getElementById('deleteTagForm').action      = btn.getAttribute('data-delete-url');
            new bootstrap.Modal(document.getElementById('deleteTagModal')).show();
        }

        /* ── Live colour preview ─────────────────────────────────────────────── */
        function updateTagPreview() {
            var preview = document.getElementById('tagPreview');
            var name    = document.getElementById('tagName')?.value.trim() || 'Tag Name';
            var bg      = document.getElementById('tagBgColor')?.value || '#6366f1';
            var fg      = document.getElementById('tagFgColor')?.value || '#ffffff';
            if (preview) {
                preview.textContent         = name || 'Tag Name';
                preview.style.backgroundColor = bg;
                preview.style.color           = fg;
            }
        }

        /* ── Sync color picker ↔ text input ──────────────────────────────────── */
        document.addEventListener('DOMContentLoaded', function () {
            var content = document.querySelector('.page-content');
            if (content) content.style.display = 'block';
            var overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.style.display = 'none';

            /* Sync bg color picker ↔ text */
            var bgColor = document.getElementById('tagBgColor');
            var bgText  = document.getElementById('tagBg');
            if (bgColor && bgText) {
                bgColor.addEventListener('input', function () { bgText.value = bgColor.value; updateTagPreview(); });
                bgText.addEventListener('input', function () {
                    if (/^#[0-9a-fA-F]{6}$/.test(bgText.value)) { bgColor.value = bgText.value; }
                    updateTagPreview();
                });
            }

            /* Sync fg color picker ↔ text */
            var fgColor = document.getElementById('tagFgColor');
            var fgText  = document.getElementById('tagFg');
            if (fgColor && fgText) {
                fgColor.addEventListener('input', function () { fgText.value = fgColor.value; updateTagPreview(); });
                fgText.addEventListener('input', function () {
                    if (/^#[0-9a-fA-F]{6}$/.test(fgText.value)) { fgColor.value = fgText.value; }
                    updateTagPreview();
                });
            }

            /* Live preview from name input */
            var nameInput = document.getElementById('tagName');
            if (nameInput) nameInput.addEventListener('input', updateTagPreview);
        });
    </script>
@endsection
