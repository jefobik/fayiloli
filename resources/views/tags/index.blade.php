@extends('layouts.app')
@section('content')
<div class="container py-4">

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h4 fw-bold text-primary mb-0">
                <i class="fas fa-tags me-2" aria-hidden="true"></i>Tags
            </h1>
            <p class="text-muted small mb-0">Manage classification tags and their colour codes</p>
        </div>
        <button type="button" class="btn btn-success btn-sm"
                data-bs-toggle="modal" data-bs-target="#addTagModal"
                aria-label="Create new tag">
            <i class="fas fa-plus me-1" aria-hidden="true"></i> New Tag
        </button>
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
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" aria-label="Tags list">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">Name</th>
                            <th scope="col">Code</th>
                            <th scope="col">Colours</th>
                            <th scope="col">Preview</th>
                            <th scope="col" class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tags as $tag)
                        <tr>
                            <td class="ps-4 fw-semibold" data-label="Name">{{ $tag->name }}</td>
                            <td data-label="Code">
                                @if ($tag->code)
                                    <code class="text-secondary" style="font-size:0.8rem">{{ $tag->code }}</code>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td data-label="Colours">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    @if ($tag->background_color)
                                        <span class="d-flex align-items-center gap-1">
                                            <span class="d-inline-block border rounded"
                                                  style="width:18px;height:18px;background:{{ $tag->background_color }}"
                                                  title="Background: {{ $tag->background_color }}"
                                                  aria-label="Background colour {{ $tag->background_color }}"></span>
                                            <code class="text-secondary" style="font-size:0.72rem">{{ $tag->background_color }}</code>
                                        </span>
                                    @endif
                                    @if ($tag->foreground_color)
                                        <span class="d-flex align-items-center gap-1">
                                            <span class="d-inline-block border rounded"
                                                  style="width:18px;height:18px;background:{{ $tag->foreground_color }}"
                                                  title="Foreground: {{ $tag->foreground_color }}"
                                                  aria-label="Foreground colour {{ $tag->foreground_color }}"></span>
                                            <code class="text-secondary" style="font-size:0.72rem">{{ $tag->foreground_color }}</code>
                                        </span>
                                    @endif
                                    @if (!$tag->background_color && !$tag->foreground_color)
                                        <span class="text-muted small">—</span>
                                    @endif
                                </div>
                            </td>
                            <td data-label="Preview">
                                <span class="badge"
                                      style="background-color:{{ $tag->background_color ?? '#6c757d' }};color:{{ $tag->foreground_color ?? '#ffffff' }}">
                                    {{ $tag->name }}
                                </span>
                            </td>
                            <td class="text-end pe-4" data-label="Actions">
                                <div class="d-flex justify-content-end gap-1" role="group"
                                     aria-label="Actions for tag {{ $tag->name }}">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            aria-label="Delete tag {{ $tag->name }}"
                                            data-tag-name="{{ $tag->name }}"
                                            data-delete-url="{{ route('tags.destroy', $tag) }}"
                                            onclick="confirmDeleteTag(this)">
                                        <i class="fas fa-trash-alt" aria-hidden="true"></i>
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
                                        data-bs-toggle="modal" data-bs-target="#addTagModal">
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
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" aria-label="Tag assignments by folder">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">Workspace / Folder</th>
                            <th scope="col">Category</th>
                            <th scope="col">Tags</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($folders as $folder)
                        <tr>
                            <td class="ps-4 fw-semibold" data-label="Folder">
                                {{ $folder->name }}
                                @foreach ($folder->subfolders as $sub)
                                    <span class="text-muted fw-normal"> / {{ $sub->name }}</span>
                                @endforeach
                            </td>
                            <td data-label="Category">
                                @forelse ($folder->categories as $category)
                                    <span class="badge bg-light text-secondary border me-1">{{ $category->name }}</span>
                                @empty
                                    <span class="text-muted small">—</span>
                                @endforelse
                            </td>
                            <td data-label="Tags">
                                @php $hasTags = false; @endphp
                                @foreach ($folder->categories as $category)
                                    @foreach ($category->tags as $tag)
                                        @php $hasTags = true; @endphp
                                        <span class="badge me-1"
                                              style="background-color:{{ $tag->background_color ?? '#6c757d' }};color:{{ $tag->foreground_color ?? '#fff' }}">
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
</div>

{{-- ── Add Tag Modal ────────────────────────────────────────────────────────── --}}
@include('folders.modals.addTag')

{{-- ── Delete Confirmation Modal ───────────────────────────────────────────── --}}
<div class="modal fade" id="deleteTagModal" tabindex="-1"
     aria-labelledby="deleteTagModalLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-danger fw-semibold" id="deleteTagModalLabel">
                    <i class="fas fa-triangle-exclamation me-2" aria-hidden="true"></i>Delete Tag
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cancel deletion"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-0 small">
                    Delete tag <strong id="deleteTagName"></strong>?
                    This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer border-0 pt-1">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteTagForm" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDeleteTag(btn) {
    document.getElementById('deleteTagName').textContent = btn.getAttribute('data-tag-name');
    document.getElementById('deleteTagForm').action = btn.getAttribute('data-delete-url');
    new bootstrap.Modal(document.getElementById('deleteTagModal')).show();
}
document.addEventListener('DOMContentLoaded', function () {
    var content = document.querySelector('.page-content');
    if (content) content.style.display = 'block';
    var overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.style.display = 'none';
});
</script>
@endsection
