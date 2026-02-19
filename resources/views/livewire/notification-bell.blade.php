<div class="relative" x-data="{ open: false }" @click.outside="open = false" wire:poll.15s="loadNotifications">

    {{-- Bell Button --}}
    <button class="header-icon-btn" @click="open = !open" title="Notifications">
        <i class="fas fa-bell" style="font-size:1.05rem"></i>
        @if($count > 0)
            <span class="notif-badge">{{ $count > 9 ? '9+' : $count }}</span>
        @endif
    </button>

    {{-- Dropdown Panel --}}
    <div class="edms-dropdown notif-dropdown"
         x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         x-cloak>

        <div class="notif-header">
            <h6>Notifications @if($count > 0)<span class="notif-badge" style="background:#7c3aed;color:#fff;font-size:0.65rem;padding:0.1rem 0.4rem;border-radius:999px;margin-left:0.35rem">{{ $count }}</span>@endif</h6>
            @if($count > 0)
                <button wire:click="dismissAll" class="auth-link" style="font-size:0.75rem;background:none;border:none;cursor:pointer">
                    Mark all read
                </button>
            @endif
        </div>

        @forelse($notifications as $notif)
            <div class="notif-item">
                <div class="notif-icon {{ $notif['color'] }}">
                    <i class="fas {{ $notif['icon'] }}"></i>
                </div>
                <div style="flex:1;min-width:0">
                    <div class="notif-msg">{{ $notif['message'] }}</div>
                    <div class="notif-time">
                        <i class="fas fa-clock" style="font-size:0.65rem;margin-right:0.2rem"></i>{{ $notif['created_at'] }}
                    </div>
                </div>
                <button wire:click="dismiss({{ $notif['id'] }})"
                        style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:0.8rem;padding:0.2rem;flex-shrink:0"
                        title="Dismiss">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @empty
            <div style="padding:2rem 1rem;text-align:center;color:#94a3b8">
                <i class="fas fa-bell-slash" style="font-size:2rem;margin-bottom:0.5rem;display:block;opacity:0.4"></i>
                <div style="font-size:0.82rem">You're all caught up!</div>
            </div>
        @endforelse

        <div class="notif-footer">
            <a href="{{ route('home') }}">View all in dashboard</a>
        </div>
    </div>
</div>
