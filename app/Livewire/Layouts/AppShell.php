<?php

declare(strict_types=1);

namespace App\Livewire\Layouts;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use App\Services\ThemeService;

class AppShell extends Component
{
    public bool   $sidebarCollapsed = false;
    public string $theme            = 'system';

    /** Preference key written to user_preferences table */
    private const SIDEBAR_PREF = 'sidebar_collapsed';

    public function mount(): void
    {
        // ── Sidebar state: DB preference → session fallback → default ────────
        // DB preference is cross-device persistent.  If user_preferences table
        // doesn't exist (central context) getPreference() catches and returns null.
        if (Auth::check()) {
            try {
                $user   = Auth::user();
                $dbPref = $user->getPreference(self::SIDEBAR_PREF);

                if ($dbPref !== null) {
                    $this->sidebarCollapsed = filter_var($dbPref, FILTER_VALIDATE_BOOLEAN);
                } else {
                    // First login or no stored pref: fall back to session (same-device)
                    $this->sidebarCollapsed = (bool) session(self::SIDEBAR_PREF, false);
                }
            } catch (\Throwable) {
                // user_preferences table not yet migrated — fall back to session.
                $this->sidebarCollapsed = (bool) session(self::SIDEBAR_PREF, false);
            }
        }

        // ── Theme: delegate to ThemeService (user → tenant default → config) ──
        $this->theme = app(ThemeService::class)->getThemePreference();
    }

    /**
     * Receive theme-updated broadcasts from GlobalThemeSwitcher so the shell
     * can react if layout logic ever depends on the active theme value.
     */
    #[On('theme-updated')]
    public function updateTheme(string $theme): void
    {
        $this->theme = $theme;
    }

    /**
     * Toggle the nav rail and persist to:
     *   1. user_preferences (DB) — cross-device durable
     *   2. session             — same-tab fast fallback
     *
     * The Alpine init() in app-shell.blade.php also mirrors this to localStorage
     * ('gw_sidebar_collapsed') so the rail renders at the correct width before
     * the first Livewire hydration, preventing rail-width layout shift.
     */
    public function toggleSidebar(): void
    {
        $this->sidebarCollapsed = !$this->sidebarCollapsed;

        if (Auth::check()) {
            Auth::user()->setPreference(
                self::SIDEBAR_PREF,
                $this->sidebarCollapsed ? '1' : '0',
            );
        }

        // Keep session in sync as a same-device fallback
        session([self::SIDEBAR_PREF => $this->sidebarCollapsed]);

        $this->dispatch('sidebar-toggled', collapsed: $this->sidebarCollapsed);
    }

    public function render(): \Illuminate\View\View
    {
        return view('tenant.components.layout.app-shell');
    }
}
