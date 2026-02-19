<div class="header-search-wrap" wire:keydown.escape="close" @click.outside="$wire.close()">
    <i class="fas fa-search search-icon"></i>
    <input
        type="search"
        wire:model.live.debounce.300ms="query"
        placeholder="Search documents, folders, tags…"
        autocomplete="off"
        @focus="$wire.updatedQuery()"
    />

    @if($isOpen && count($results))
        <div style="
            position:absolute; top:calc(100% + 6px); left:0; right:0;
            background:#fff; border:1px solid #e2e8f0; border-radius:12px;
            box-shadow:0 12px 40px rgba(0,0,0,0.12); z-index:200; overflow:hidden;
            max-height:380px; overflow-y:auto;
        ">
            @php
                $grouped = collect($results)->groupBy('type');
            @endphp
            @foreach($grouped as $type => $items)
                <div style="padding:0.4rem 0.9rem 0.15rem;font-size:0.65rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em">
                    {{ ucfirst($type) }}s
                </div>
                @foreach($items as $result)
                    <div
                        wire:click="selectResult('{{ $result['url'] }}', {{ $result['folder_id'] ?? 'null' }})"
                        style="display:flex;align-items:center;gap:0.75rem;padding:0.6rem 0.9rem;cursor:pointer;transition:background 0.1s"
                        onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''"
                    >
                        <div style="width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:#f1f5f9;flex-shrink:0">
                            <i class="fas {{ $result['icon'] }} {{ $result['color'] }}" style="font-size:0.85rem"></i>
                        </div>
                        <div style="flex:1;min-width:0">
                            <div style="font-size:0.85rem;font-weight:600;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $result['label'] }}</div>
                            <div style="font-size:0.72rem;color:#94a3b8">{{ $result['sub'] }}</div>
                        </div>
                        <i class="fas fa-arrow-right" style="color:#d1d5db;font-size:0.7rem;flex-shrink:0"></i>
                    </div>
                @endforeach
            @endforeach
        </div>
    @endif

    @if(strlen(trim($query)) >= 2 && count($results) === 0)
        <div style="
            position:absolute; top:calc(100% + 6px); left:0; right:0;
            background:#fff; border:1px solid #e2e8f0; border-radius:12px;
            padding:1.5rem; text-align:center; color:#94a3b8; font-size:0.85rem;
            box-shadow:0 12px 40px rgba(0,0,0,0.12); z-index:200;
        ">
            <i class="fas fa-search" style="font-size:1.5rem;opacity:0.3;display:block;margin-bottom:0.5rem"></i>
            No results for "{{ $query }}"
        </div>
    @endif
</div>

@script
<script>
    $wire.on('navigate-to-folder', ({ url, folderId }) => {
        if (typeof fetchFiles === 'function') {
            fetchFiles(url, 'folder');
        } else {
            window.location.href = url;
        }
    });
</script>
@endscript
