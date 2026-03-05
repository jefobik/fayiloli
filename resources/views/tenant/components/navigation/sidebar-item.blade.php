{{--
_sidebar-item.blade.php — DRY nav item partial
─────────────────────────────────────────────────────────────────────────────
Params:
$href (string) — Route URL
$icon (string) — FA icon class, e.g. 'fa-house-chimney'
$label (string) — Human-readable label shown when expanded
$active (bool) — Whether this item is the current page
$tooltip (string) — Text shown as tooltip when collapsed
$aria (string) — Raw HTML attribute string (e.g. aria-current="page")

Active indicator:
• A 3px vertical bar on the left edge (not a filled background chip)
• Active icon uses --tenant-primary colour for brand coherence
• Background remains transparent — only the bar + text colour change
• This matches Google Drive's left-navigation active pattern exactly
─────────────────────────────────────────────────────────────────────────────
--}}
<a href="{{ $href }}" @if(!str_contains($href, '#')) wire:navigate @endif data-nav-item class="gw-sidebar-item group relative flex items-center text-[0.8125rem] font-medium
          w-full overflow-hidden no-underline
          transition-colors duration-150
          focus-visible:outline-none focus-visible:ring-2
          focus-visible:ring-[var(--tenant-primary)] focus-visible:ring-inset
          {{ $active
    ? 'text-[var(--tenant-primary)] bg-[var(--tenant-primary-muted)]'
    : 'text-[var(--text-muted)] hover:bg-[var(--gw-surface-hover)] hover:text-[var(--text-main)]' }}" :class="railExpanded
       ? 'gap-3 pl-4 pr-3 py-2 rounded-[var(--radius-sm)] mx-2'
       : 'justify-center pl-0 pr-0 py-2.5 mx-0 rounded-none'"
    x-tooltip.placement.right="!railExpanded ? '{{ addslashes($tooltip) }}' : false" {!! $aria !!}>

    {{-- ── Vertical active bar (left edge, visible only when active) ── --}}
    @if ($active)
        <span class="absolute left-0 top-[20%] bottom-[20%] w-[3px]
                         rounded-r-full bg-[var(--tenant-primary)]
                         transition-all duration-200" aria-hidden="true"></span>
    @endif

    {{-- ── Icon ── --}}
    <i class="fa-solid {{ $icon }} shrink-0 w-5 text-center text-[0.9rem]
               transition-colors
               {{ $active
    ? 'text-[var(--tenant-primary)]'
    : 'text-[var(--text-ghost)] group-hover:text-[var(--text-main)]' }}" aria-hidden="true"></i>

    {{-- ── Label (expanded only) ── --}}
    <span x-show="railExpanded" x-cloak class="whitespace-nowrap flex-1 truncate leading-none">
        {{ $label }}
    </span>
</a>