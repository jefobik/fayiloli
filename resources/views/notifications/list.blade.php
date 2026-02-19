@forelse($data as $notification)
    <div class="notif-item" data-id="{{ $notification->_id ?? $notification->id }}">
        <div style="flex:1;min-width:0">
            <div class="notif-msg">{{ $notification->message }}</div>
            <div class="notif-time">
                <i class="fas fa-clock" style="font-size:0.65rem;margin-right:0.2rem"></i>
                {{ $notification->created_at?->diffForHumans() }}
            </div>
        </div>
    </div>
@empty
    <div style="padding:1rem;text-align:center;color:#94a3b8;font-size:0.82rem">
        No notifications
    </div>
@endforelse
