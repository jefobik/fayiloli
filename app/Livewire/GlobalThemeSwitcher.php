<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Services\ThemeService;

class GlobalThemeSwitcher extends Component
{
    /** Current preference (light | dark | system) — drives blade active state */
    public string $theme = 'system';

    /** Tenant context — carried in component for event payloads */
    public ?string $tenantId      = null;
    public bool    $isTenantContext = false;

    public function mount(): void
    {
        /** @var ThemeService $svc */
        $svc = app(ThemeService::class);

        $this->theme = $svc->getThemePreference();

        if ($svc->isTenantContext()) {
            $this->isTenantContext = true;
            $this->tenantId        = $svc->getCurrentTenant()?->id;
        }
    }

    /**
     * Persist a theme choice and broadcast the change to all listeners.
     *
     * Called by:
     *   - Wire button clicks (x-click in blade)
     *   - Keyboard cycle shortcut via @cycle-theme.window → $wire.updateTheme()
     */
    public function updateTheme(string $theme): void
    {
        /** @var ThemeService $svc */
        $svc = app(ThemeService::class);

        if (!$svc->isValidTheme($theme)) {
            return; // silently ignore invalid values from forged events
        }

        if ($this->theme === $theme) {
            return; // no-op — prevents unnecessary DB write and Livewire round-trip
        }

        $svc->setThemePreference($theme);
        $this->theme = $theme;

        // Broadcast to all Livewire components AND as a window event so
        // the Alpine themeManager picks it up and calls applyTheme().
        $this->dispatch('theme-updated',
            theme:         $theme,
            timestamp:     now()->timestamp,
            tenantId:      $this->tenantId,
            tenantContext: $this->isTenantContext,
        );
    }

    /**
     * Reset to system (device) preference — wired to the "Reset" button.
     */
    public function resetToSystem(): void
    {
        $this->updateTheme('system');
    }

    /**
     * All available themes with display metadata.
     *
     * @return array<string, array{key: string, label: string, icon: string, isActive: bool}>
     */
    #[Computed]
    public function availableThemes(): array
    {
        /** @var ThemeService $svc */
        $svc    = app(ThemeService::class);
        $result = [];

        foreach ($svc->getAvailableThemes() as $key) {
            $result[$key] = [
                'key'      => $key,
                'label'    => $svc->getThemeLabel($key),
                'icon'     => $svc->getThemeIcon($key),
                'isActive' => $this->theme === $key,
            ];
        }

        return $result;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.global-theme-switcher', [
            'availableThemes' => $this->availableThemes,
        ]);
    }
}
