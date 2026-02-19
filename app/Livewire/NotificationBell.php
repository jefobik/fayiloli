<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationBell extends Component
{
    public int $count = 0;
    public array $notifications = [];

    public function mount(): void
    {
        $this->loadNotifications();
    }

    #[On('notification-created')]
    public function loadNotifications(): void
    {
        $query = Notification::where('status', 'UNREAD')
            ->where('dismiss_status', 'UNDISMISSED')
            ->latest()
            ->limit(8);

        $items = $query->get();
        $this->count = $items->count();
        $this->notifications = $items->map(fn($n) => [
            'id'            => $n->id,
            'message'       => $n->message,
            'activity_type' => $n->activity_type,
            'created_at'    => $n->created_at?->diffForHumans(),
            'icon'          => $this->getIcon($n->activity_type),
            'color'         => $this->getColor($n->activity_type),
        ])->toArray();
    }

    public function dismiss(int $id): void
    {
        Notification::where('id', $id)->update([
            'status'          => 'READ',
            'dismiss_status'  => 'DISMISSED',
        ]);
        $this->loadNotifications();
    }

    public function dismissAll(): void
    {
        Notification::where('status', 'UNREAD')
            ->where('dismiss_status', 'UNDISMISSED')
            ->update(['status' => 'READ', 'dismiss_status' => 'DISMISSED']);
        $this->loadNotifications();
    }

    private function getIcon(string $type): string
    {
        return match(true) {
            str_contains($type, 'document') || str_contains($type, 'upload') => 'fa-file-alt',
            str_contains($type, 'folder')   => 'fa-folder',
            str_contains($type, 'share')    => 'fa-share-alt',
            str_contains($type, 'request')  => 'fa-paper-plane',
            str_contains($type, 'note')     => 'fa-sticky-note',
            default                         => 'fa-bell',
        };
    }

    private function getColor(string $type): string
    {
        return match(true) {
            str_contains($type, 'document') => 'bg-blue-50 text-blue-600',
            str_contains($type, 'folder')   => 'bg-amber-50 text-amber-600',
            str_contains($type, 'share')    => 'bg-purple-50 text-purple-600',
            str_contains($type, 'request')  => 'bg-emerald-50 text-emerald-600',
            default                         => 'bg-slate-100 text-slate-600',
        };
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
