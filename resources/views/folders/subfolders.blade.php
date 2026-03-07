<ul class="subfolders" style="display: none;">
    @foreach ($folder->subfolders as $subfolder)
        <li>
            <a href="#" data-url="{{ route('folders.show', $subfolder) }}"
                onclick="fetchFiles('{{ route('folders.show', $subfolder) }}', 'folder')">
                <i class="fas fa-folder folder-icon"></i>
                <span class="folder-name">{{ $subfolder->name }}</span>
            </a>
        </li>
    @endforeach
</ul>