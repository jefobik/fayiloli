<?php

namespace App\Livewire\Layouts;

use Livewire\Component;

use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class AppShell extends Component
{
    public bool $sidebarCollapsed = false;
    public string $theme = 'system';

    public function mount()
    {
        $this->sidebarCollapsed = session('sidebarCollapsed', false);

        if (Auth::check()) {
            $this->theme = Auth::user()->theme ?? 'system';
        }
    }

    #[On('theme-updated')]
    public function updateTheme($theme)
    {
        $this->theme = $theme;
    }

    public function toggleSidebar()
    {
        $this->sidebarCollapsed = !$this->sidebarCollapsed;
        session(['sidebarCollapsed' => $this->sidebarCollapsed]);
    }

    public function render()
    {
        return view('livewire.layouts.app-shell');
    }
}
