/**
 * Alpine.js Theme Manager Component
 *
 * Provides client-side theme management with:
 * - Automatic theme application
 * - localStorage sync (tenant-aware key namespacing)
 * - System preference detection
 * - Keyboard shortcuts
 * - Motion preference detection
 * - Smooth transitions
 * - Multi-tenancy support (stancl/tenancy)
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('themeManager', () => ({
        currentTheme: window.__themePreference || 'system',
        isDarkMode: window.__isDarkMode || false,
        prefersReducedMotion: window.__prefersReducedMotion || false,
        isTransitioning: false,
        tenantId: window.__tenantContext?.tenantId || null,
        isTenantContext: window.__tenantContext?.isTenantContext || false,

        init() {
            this.setupEventListeners();
            this.setupKeyboardShortcuts();
            this.setupSystemPreferenceListener();
            this.applyTheme(this.currentTheme);
        },

        /**
         * Get tenant-aware localStorage key
         * Ensures each tenant's theme preference is stored separately
         */
        getStorageKey() {
            if (this.isTenantContext && this.tenantId) {
                return `theme_preference_${this.tenantId}`;
            }
            return 'theme_preference_central';
        },

        /**
         * Setup event listeners for theme updates
         */
        setupEventListeners() {
            // Listen for Livewire theme-updated event
            window.addEventListener('theme-updated', (event) => {
                const { detail } = event;
                if (detail && detail.theme) {
                    this.currentTheme = detail.theme;
                    this.applyTheme(detail.theme);
                    this.saveToLocalStorage();
                }
            });

            // Listen for Livewire Alpine dispatch events
            document.addEventListener('livewire:updated', () => {
                // Refresh theme after Livewire updates
                this.applyTheme(this.currentTheme);
            });
        },

        /**
         * Setup keyboard shortcuts (Ctrl+Shift+T to toggle theme)
         */
        setupKeyboardShortcuts() {
            if (!this.getConfig('keyboard_shortcuts')) {
                return;
            }

            document.addEventListener('keydown', (e) => {
                // Ctrl+Shift+T or Cmd+Shift+T
                if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'T') {
                    e.preventDefault();
                    this.cycleTheme();
                }
            });
        },

        /**
         * Listen for system preference changes
         */
        setupSystemPreferenceListener() {
            const prefersColorScheme = window.matchMedia('(prefers-color-scheme: dark)');

            prefersColorScheme.addEventListener('change', (e) => {
                if (this.currentTheme === 'system') {
                    this.isDarkMode = e.matches;
                    this.applyTheme('system');
                }
            });

            // Listen for prefers-reduced-motion changes
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
            prefersReducedMotion.addEventListener('change', (e) => {
                this.prefersReducedMotion = e.matches;
                this.updateTransitionSettings();
            });
        },

        /**
         * Apply theme to DOM
         */
        applyTheme(theme) {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = theme === 'dark' || (theme === 'system' && prefersDark);

            // Start transition
            if (!this.prefersReducedMotion && this.getConfig('smooth_transitions')) {
                this.startTransition();
            }

            // Update localStorage for persistence (tenant-aware)
            this.saveToLocalStorage(theme);

            // Apply classes and attributes
            this.setDarkMode(isDark);
            this.setDataAttributes(theme, isDark);

            // Dispatch custom event for other components
            window.dispatchEvent(new CustomEvent('theme-applied', {
                detail: {
                    theme,
                    isDark,
                    timestamp: Date.now(),
                    tenantId: this.tenantId,
                    isTenantContext: this.isTenantContext
                }
            }));

            // End transition
            if (!this.prefersReducedMotion) {
                setTimeout(() => this.endTransition(), 150);
            }

            this.isDarkMode = isDark;
        },

        /**
         * Set dark mode on document
         */
        setDarkMode(isDark) {
            const html = document.documentElement;
            const body = document.body;

            if (isDark) {
                html.classList.add('dark', 'dark-mode');
                body.classList.add('dark-mode');
            } else {
                html.classList.remove('dark', 'dark-mode');
                body.classList.remove('dark-mode');
            }
        },

        /**
         * Set data attributes for CSS and JS
         */
        setDataAttributes(theme, isDark) {
            const html = document.documentElement;
            html.setAttribute('data-theme', theme);
            html.setAttribute('data-theme-preference', theme);
            html.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
            html.setAttribute('data-current-theme', isDark ? 'dark' : 'light');

            // Add tenant context to DOM
            if (this.isTenantContext) {
                html.setAttribute('data-tenancy-context', 'tenant');
                html.setAttribute('data-tenant-id', this.tenantId);
            } else {
                html.setAttribute('data-tenancy-context', 'central');
            }
        },

        /**
         * Cycle through themes: system → light → dark → system.
         *
         * Three entry points funnel through here:
         *   • Ctrl+Shift+T keyboard shortcut (setupKeyboardShortcuts)
         *   • Header topbar quick-toggle button
         *   • Any caller that dispatches window 'cycle-theme' event manually
         *
         * We perform the DOM apply immediately (no round-trip latency) then
         * persist via the 'cycle-theme' window event which the GlobalThemeSwitcher
         * blade bridges to $wire.updateTheme() → DB write → theme-updated broadcast.
         */
        cycleTheme() {
            const themes    = ['system', 'light', 'dark'];
            const current   = this.currentTheme;
            const nextTheme = themes[(themes.indexOf(current) + 1) % themes.length];

            // ── Optimistic client-side update (instant, no waiting for Livewire) ──
            this.currentTheme            = nextTheme;
            window.__themePreference     = nextTheme;   // keeps header icon in sync
            this.applyTheme(nextTheme);                 // applies CSS classes immediately

            // ── Persist via GlobalThemeSwitcher Livewire component ───────────────
            // The @cycle-theme.window listener on the blade calls $wire.updateTheme()
            // which writes to DB and re-dispatches 'theme-updated' for full sync.
            window.dispatchEvent(new CustomEvent('cycle-theme', {
                detail: { nextTheme },
            }));
        },

        /**
         * Start theme transition animation
         */
        startTransition() {
            this.isTransitioning = true;
            const html = document.documentElement;
            html.setAttribute('data-theme-transition', 'enabled');
            html.classList.add('theme-transition');
        },

        /**
         * End theme transition animation
         */
        endTransition() {
            this.isTransitioning = false;
            const html = document.documentElement;
            html.removeAttribute('data-theme-transition');
            html.classList.remove('theme-transition');
        },

        /**
         * Update transition settings based on motion preference
         */
        updateTransitionSettings() {
            if (this.prefersReducedMotion) {
                document.documentElement.style.setProperty('--transition-duration', '0ms');
            } else {
                document.documentElement.style.setProperty(
                    '--transition-duration',
                    '150ms'
                );
            }
        },

        /**
         * Save current theme to localStorage (tenant-aware key)
         */
        saveToLocalStorage(theme = null) {
            const themeToSave = theme || this.currentTheme;
            const key = this.getStorageKey();
            localStorage.setItem(key, themeToSave);
        },

        /**
         * Get config value from window or defaults
         */
        getConfig(key) {
            const defaults = {
                keyboard_shortcuts: true,
                smooth_transitions: true,
                system_preference_detection: true,
                local_storage_sync: true,
            };

            return window.__themeConfig?.[key] ?? defaults[key] ?? true;
        },

        /**
         * Get human-readable theme label
         */
        getThemeLabel(theme) {
            return {
                light: 'Light Mode',
                dark: 'Dark Mode',
                system: 'System Preference',
            }[theme] || theme;
        },

        /**
         * Reset to system preference
         */
        resetToSystem() {
            this.currentTheme = 'system';
            this.applyTheme('system');
            const key = this.getStorageKey();
            localStorage.removeItem(key);
        },

        /**
         * Get tenant context info for logging
         */
        getTenancyInfo() {
            return {
                isTenantContext: this.isTenantContext,
                tenantId: this.tenantId,
                storageKey: this.getStorageKey(),
                currentTheme: this.currentTheme,
            };
        }
    }));
});
