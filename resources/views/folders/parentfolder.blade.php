@if (!$folder->parent)
    <li class="folder-item" data-folder-id="{{ $folder->id }}">
        <a href="#" data-url="{{ route('folders.show', $folder) }}"
            onclick="fetchFiles('{{ route('folders.show', $folder) }}', 'folder')">
            <span class="folder-content">
                <i class="fas fa-folder folder-icon"></i>
                <span class="folder-name">{{ $folder->name }}</span>
            </span>
        </a>
        @if ($folder->subfolders->isNotEmpty())
            <button class="toggle-subfolders-btn" onclick="toggleSubfolders(this)">
                <i class="fas fa-chevron-down"></i>
            </button>
            @include('folders.subfolders')
        @endif
    </li>
@endif