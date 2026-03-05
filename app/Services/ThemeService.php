<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

/**
 * ThemeService
 *
 * Tenant-aware service for managing user theme preferences across the application.
 * Handles detection, persistence, and application of theme settings with support for:
 * - Light / Dark / System (auto) modes
 * - Central admin and tenant workspace contexts
 * - Per-tenant or global theme preferences
 * - Persistent storage in user model
 * - System preference detection (prefers-color-scheme)
 * - Browser localStorage sync as fallback
 */
class ThemeService
{
    public const THEME_LIGHT = 'light';
    public const THEME_DARK = 'dark';
    public const THEME_SYSTEM = 'system';

    private const STORAGE_KEY = 'user_theme_preference';
    private const AVAILABLE_THEMES = [self::THEME_LIGHT, self::THEME_DARK, self::THEME_SYSTEM];
    /**
     * Check if currently in a tenant context
     */
    public function isTenantContext(): bool
    {
        return tenancy()->initialized;
    }

    /**
     * Get the current tenant or null if in central context
     */
    public function getCurrentTenant()
    {
        return tenancy()->initialized ? tenancy()->tenant : null;
    }

    /**
     * Get the resolved theme for the current user.
     * Returns the actual applied theme ('light' or 'dark'), not the preference setting.
     */
    public function getResolvedTheme(): string
    {
        $preference = $this->getThemePreference();

        if ($preference === self::THEME_SYSTEM) {
            // System mode: will be resolved client-side based on OS preference
            return self::THEME_SYSTEM;
        }

        return in_array($preference, self::AVAILABLE_THEMES) ? $preference : self::THEME_LIGHT;
    }

    /**
     * Get the user's theme preference.
     *
     * Resolution order (highest → lowest priority):
     *   1. Authenticated user's persisted column  (users.theme)
     *   2. Tenant-level workspace default         (tenant.settings.default_theme)
     *   3. Global application default             (config theme.default)
     *
     * Works in both central admin and tenant contexts.
     */
    public function getThemePreference(): string
    {
        // 1. Authenticated user preference — fastest, most specific
        if (Auth::check()) {
            $user      = Auth::user();
            $userTheme = ($user instanceof Model) ? ($user->theme ?? null) : null;

            if ($userTheme && in_array($userTheme, self::AVAILABLE_THEMES)) {
                return $userTheme;
            }
        }

        // 2. Tenant-level workspace default (set by tenant admin in settings)
        if ($this->isTenantContext()) {
            $tenantDefault = $this->getCurrentTenant()?->settings['default_theme'] ?? null;
            if ($tenantDefault && in_array($tenantDefault, self::AVAILABLE_THEMES)) {
                return $tenantDefault;
            }
        }

        // 3. Global config default (config/theme.php → 'system')
        $configDefault = config('theme.default', self::THEME_SYSTEM);
        return in_array($configDefault, self::AVAILABLE_THEMES) ? $configDefault : self::THEME_SYSTEM;
    }

    /**
     * Set the user's theme preference and persist it.
     * Works in both central admin and tenant contexts.
     *
     * @throws \InvalidArgumentException If theme is not valid
     */
    public function setThemePreference(string $theme): void
    {
        if (!in_array($theme, self::AVAILABLE_THEMES)) {
            throw new \InvalidArgumentException(
                "Invalid theme '{$theme}'. Must be one of: " . implode(', ', self::AVAILABLE_THEMES)
            );
        }

        // Persist to authenticated user if available
        // Works whether in central db or tenant db
        if (Auth::check()) {
            $user = Auth::user();
            if ($user instanceof Model) {
                // Use fill and save for better data integrity
                /** @var \Illuminate\Database\Eloquent\Model $user */
                $user->fill(['theme' => $theme])->save();
            }
        }
    }

    /**
     * Check if a theme preference is valid.
     */
    public function isValidTheme(string $theme): bool
    {
        return in_array($theme, self::AVAILABLE_THEMES);
    }

    /**
     * Get all available theme options.
     */
    public function getAvailableThemes(): array
    {
        return self::AVAILABLE_THEMES;
    }

    /**
     * Get human-readable theme label.
     */
    public function getThemeLabel(string $theme): string
    {
        return match ($theme) {
            self::THEME_LIGHT => 'Light',
            self::THEME_DARK => 'Dark',
            self::THEME_SYSTEM => 'System',
            default => 'Unknown',
        };
    }

    /**
     * Get the FA icon class for a theme.
     */
    public function getThemeIcon(string $theme): string
    {
        return match ($theme) {
            self::THEME_LIGHT => 'fas fa-sun',
            self::THEME_DARK => 'fas fa-moon',
            self::THEME_SYSTEM => 'fas fa-desktop',
            default => 'fas fa-palette',
        };
    }

    /**
     * Generate the inline bootstrap script that prevents FOUC (Flash of Unstyled Content)
     * AND seeds all client-side globals (__themeConfig, __tenantContext) in a SINGLE
     * synchronous <script> block so downstream JS (theme-manager.js, Alpine) can rely on
     * them being present from the very first paint.
     *
     * Key fix: uses the PHP-computed, tenant-aware localStorage key so it matches exactly
     * what theme-manager.js writes (theme_preference_{tenantId} / theme_preference_central).
     *
     * @param string  $currentTheme   Server-side resolved preference (light|dark|system)
     * @param bool    $includeScriptTags Wrap output in <script> tags
     */
    public function generateThemeBootstrapScript(string $currentTheme, bool $includeScriptTags = true): string
    {
        // ── Compute tenant-aware storage key in PHP (matches theme-manager.js exactly) ──
        $tenant        = $this->getCurrentTenant();
        $isTenant      = $this->isTenantContext();
        $tenantId      = $tenant?->id;
        $storageKey    = $tenantId ? "theme_preference_{$tenantId}" : 'theme_preference_central';

        // ── Serialise config blobs for JS injection ──────────────────────────────────
        $themeFeatures  = json_encode(config('theme.features', []), JSON_UNESCAPED_UNICODE);
        $tenantContext  = json_encode([
            'isTenantContext' => $isTenant,
            'tenantId'        => $tenantId,
            'tenantType'      => $tenant?->tenant_type?->value ?? null,
            'contextType'     => $isTenant ? 'tenant' : 'central',
        ], JSON_UNESCAPED_UNICODE);

        $script = <<<JS
(function () {
    // ── 1. Seed globals synchronously BEFORE any CSS paint ──────────────────
    window.__themeConfig   = {$themeFeatures};
    window.__tenantContext = {$tenantContext};

    // ── 2. Motion & colour-scheme probes ────────────────────────────────────
    var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var prefersDark    = window.matchMedia('(prefers-color-scheme: dark)').matches;

    // ── 3. Resolve preference: localStorage (tenant-namespaced) → server pref ──
    var storageKey = '{$storageKey}';
    var stored     = null;
    try { stored = localStorage.getItem(storageKey); } catch (_) {}
    var theme = (stored === 'light' || stored === 'dark' || stored === 'system')
                    ? stored
                    : '{$currentTheme}';

    // ── 4. Determine active dark/light state ────────────────────────────────
    var isDark = (theme === 'dark') || (theme === 'system' && prefersDark);

    // ── 5. Apply to <html> immediately — no layout shift, no FOUC ───────────
    var html = document.documentElement;
    if (isDark) {
        html.classList.add('dark', 'dark-mode');
        html.setAttribute('data-theme', 'dark');
        html.setAttribute('data-bs-theme', 'dark');
    } else {
        html.classList.remove('dark', 'dark-mode');
        html.setAttribute('data-theme', 'light');
        html.setAttribute('data-bs-theme', 'light');
    }

    // ── 6. Opt-in to CSS transition class only if motion is allowed ──────────
    if (!prefersReduced) {
        html.setAttribute('data-theme-transition', 'enabled');
    }

    // ── 7. Expose resolved values for Alpine / theme-manager.js ─────────────
    window.__themePreference      = theme;
    window.__isDarkMode           = isDark;
    window.__prefersReducedMotion = prefersReduced;
}());
JS;

        if ($includeScriptTags) {
            return "<script>{$script}</script>";
        }

        return $script;
    }

    /**
     * Get data attributes for the HTML element.
     */
    public function getHtmlAttributes(): array
    {
        $preference = $this->getThemePreference();

        return [
            'data-theme' => $preference,
            'data-theme-preference' => $preference,
            'data-tenancy-context' => $this->isTenantContext() ? 'tenant' : 'central',
            'class' => $this->getResolvedTheme() === self::THEME_DARK ? 'dark dark-mode' : '',
        ];
    }

    /**
     * Get context information for debugging/logging
     */
    public function getContextInfo(): array
    {
        return [
            'is_tenant_context' => $this->isTenantContext(),
            'tenant_id' => $this->getCurrentTenant()?->id ?? null,
            'tenant_slug' => $this->getCurrentTenant()?->data['slug'] ?? null,
            'user_id' => Auth::id(),
            'theme_preference' => $this->getThemePreference(),
        ];
    }
}
