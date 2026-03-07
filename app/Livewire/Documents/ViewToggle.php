<?php

namespace App\Livewire\Documents;

use Livewire\Component;
use App\Models\UserPreference;
use Illuminate\Support\Facades\Auth;

class ViewToggle extends Component
{
    public string $viewMode = 'grid';

    public function mount()
    {
        if (Auth::check()) {
            $pref = UserPreference::where('user_id', Auth::id())
                ->where('key', 'docViewMode')
                ->first();

            if ($pref) {
                $this->viewMode = $pref->value ?? 'grid';
            }
        }
    }

    public function setViewMode(string $mode = 'grid')
    {
        $this->viewMode = $mode;

        if (Auth::check()) {
            UserPreference::updateOrCreate(
                ['user_id' => Auth::id(), 'key' => 'docViewMode'],
                ['value' => $mode]
            );
        }

        // Dispatch browser event to inform Alpine
        $this->dispatch('view-mode-updated', mode: $mode);
    }

    public function render()
    {
        return view('livewire.documents.view-toggle');
    }
}
