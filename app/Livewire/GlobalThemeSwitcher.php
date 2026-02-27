<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class GlobalThemeSwitcher extends Component
{
    public string $theme = 'system';

    public function mount()
    {
        if (Auth::check()) {
            $this->theme = Auth::user()->theme ?? 'system';
        }
    }

    public function updateTheme($theme)
    {
        if (!in_array($theme, ['light', 'dark', 'system'])) {
            return;
        }

        $this->theme = $theme;

        if (Auth::check()) {
            $user = Auth::user();
            $user->theme = $theme;
            $user->save();
        }

        $this->dispatch('theme-updated', theme: $theme);
    }

    public function render()
    {
        return view('livewire.global-theme-switcher');
    }
}
