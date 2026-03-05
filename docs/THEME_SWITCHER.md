# Global Theme Switcher - Comprehensive Refactoring Guide

## Overview

This document outlines the comprehensive refactoring of the global theme switcher system implemented as part of a UX/Architecture improvement initiative. The new system provides better accessibility, smoother transitions, and a more maintainable codebase.

## Architecture Overview

### Components

#### 1. **ThemeService** (`app/Services/ThemeService.php`)
Centralized service for all theme-related operations:
- Theme preference management (get/set)
- Theme validation
- System preference detection
- Bootstrap script generation
- HTML attributes generation

**Key Methods:**
- `getThemePreference()` - Get user's current preference
- `setThemePreference($theme)` - Persist theme preference
- `getResolvedTheme()` - Get the actual applied theme
- `generateThemeBootstrapScript($theme)` - Prevent FOUC
- `getAvailableThemes()` - List all available themes

#### 2. **GlobalThemeSwitcher** (Livewire Component)
Handles theme switching logic with improved state management:
- Uses `ThemeService` for all operations
- Validates theme changes
- Dispatches events for UI updates
- Provides computed properties for view

**Features:**
- Error handling with try-catch
- Duplicate update prevention
- Event-driven architecture
- Loading states for better UX

#### 3. **theme-manager.js** (Alpine.js Component)
Client-side theme management with:
- Automatic theme application
- System preference detection
- Keyboard shortcuts (Ctrl+Shift+T)
- Motion preference detection
- localStorage sync
- Custom events dispatch

**Capabilities:**
- Cycle through themes with keyboard shortcut
- Respect `prefers-reduced-motion` media query
- Sync theme across browser tabs
- Smooth transitions

#### 4. **Configuration** (`config/theme.php`)
Centralized theme configuration:

```php
return [
    'available' => ['light', 'dark', 'system'],
    'default' => 'system',
    'storage' => [
        'user_model_field' => 'theme',
        'local_storage_key' => 'theme_preference',
        'session_key' => 'user_theme',
    ],
    'transitions' => [
        'enabled' => true,
        'duration' => 150,  // milliseconds
        'easing' => 'ease-in-out',
        'respect_prefers_reduced_motion' => true,
    ],
    'features' => [
        'keyboard_shortcuts' => true,
        'system_preference_detection' => true,
        'local_storage_sync' => true,
        'persist_to_database' => true,
        'smooth_transitions' => true,
    ],
];
```

### CSS Animations (`resources/css/app.css`)

Comprehensive theme transition animations:

- **Theme Switch Animation** (`themeSwitch`)
  - Subtle opacity and brightness adjustment
  - Duration: 150ms
  - Respects `prefers-reduced-motion`

- **Icon Rotation** (`iconRotate`)
  - 360-degree rotation on theme change
  - Smooth visual feedback

- **Dropdown Animation** (`slideDown`)
  - Entrance animation for theme selector
  - Smooth opacity and position change

- **Button State Animations**
  - Active button scaling
  - Ripple effect on click
  - Focus-visible outline

## Accessibility Features

### 1. **WCAG 2.1 Compliance**

✅ **Keyboard Navigation**
- Tab through theme options
- Enter/Space to activate
- Ctrl+Shift+T to cycle themes
- Focus indicators with proper contrast

✅ **Screen Reader Support**
- Semantic HTML buttons
- `aria-pressed` attributes
- Descriptive labels and titles
- Hidden icon labels with `aria-hidden`

✅ **Motion Preferences**
- `prefers-reduced-motion` detection
- Animations disabled for users with vestibular disorders
- Smooth transitions disabled when motion is reduced

✅ **Color Contrast**
- All text meets WCAG AA standards
- Sufficient contrast in both light and dark modes

✅ **Focus Management**
- 2px solid outline for focus-visible
- Outline offset for better visibility
- Proper tab order
- Auto-close dropdown on selection

### 2. **Assistive Technology**

**ARIA Attributes:**
- `aria-pressed` - Indicates selected theme button
- `aria-label` - Short description for icon buttons
- `aria-hidden` - Decorative icons excluded from accessibility tree
- `role` attributes where needed

**Labels:**
- Descriptive text for each theme option
- Keyboard shortcut hint ("Ctrl+Shift+T")
- Loading indicators with text labels

### 3. **System Preferences**

The theme system respects:
- `prefers-color-scheme` - Auto-detect dark/light preference
- `prefers-reduced-motion` - Disable animations for motion sensitivity
- System theme changes detected and applied automatically

## Features

### 1. **Smooth Transitions**

CSS variable `--transition-duration` (150ms by default):
```css
transition: background-color var(--transition-duration) var(--transition-timing),
            color var(--transition-duration) var(--transition-timing),
            border-color var(--transition-duration) var(--transition-timing);
```

Respects `prefers-reduced-motion` by setting `--transition-duration: 0ms`

### 2. **Keyboard Shortcuts**

- **Ctrl+Shift+T** (or Cmd+Shift+T) - Cycle through themes
  - system → light → dark → system

### 3. **Persistence**

Theme preference persisted through:
1. **Database** - User model `theme` column
2. **localStorage** - Browser-level persistence
3. **Session** - Fallback storage option

### 4. **FOUC Prevention** (Flash of Unstyled Content)

Inline bootstrap script runs synchronously before CSS:
```php
$themeService->generateThemeBootstrapScript($theme)
```

This prevents any theme flashing on page load.

### 5. **Event System**

- `theme-updated` - Fired when theme changes
- `theme-applied` - Fired when theme is applied to DOM
- `theme-update-failed` - Fired on error
- `cycle-theme` - Custom event for cycling themes

## Usage

### Using ThemeService

```php
use App\Services\ThemeService;

$themeService = app(ThemeService::class);

// Get current preference
$preference = $themeService->getThemePreference(); // 'system', 'light', or 'dark'

// Set preference
$themeService->setThemePreference('dark');

// Validate
if ($themeService->isValidTheme($theme)) {
    // ...
}

// Get all available
$themes = $themeService->getAvailableThemes();

// Generate bootstrap script
$script = $themeService->generateThemeBootstrapScript('system');
```

### In Livewire Views

```blade
<livewire:global-theme-switcher />
```

### In Blade Views

```blade
@php
    $themeService = app(\App\Services\ThemeService::class);
    $theme = $themeService->getThemePreference();
@endphp

{!! $themeService->generateThemeBootstrapScript($theme) !!}
```

### JavaScript Access

```javascript
// Theme manager initialization
document.addEventListener('alpine:init', () => {
    // Alpine component available
});

// Listen for theme changes
window.addEventListener('theme-applied', (e) => {
    const { theme, isDark } = e.detail;
    console.log(`Theme applied: ${theme}, Dark: ${isDark}`);
});

// Cycle through themes
window.dispatchEvent(new CustomEvent('cycle-theme', {
    detail: { nextTheme: 'dark' }
}));
```

## Migration / Setup

### 1. Ensure User Model has 'theme' column

The user model already has the `theme` column in the fillable array. If using a fresh install:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('theme')->default('system')->nullable();
});
```

### 2. Update Environment

Add to `.env` if needed (optional):
```
THEME_DEFAULT=system
THEME_TRANSITIONS_ENABLED=true
THEME_KEYBOARD_SHORTCUTS=true
```

### 3. Cache Config

```bash
php artisan config:cache
```

### 4. Publish Assets

Already included in the project:
- `app/Services/ThemeService.php`
- `config/theme.php`
- `resources/js/theme-manager.js`
- `resources/css/app.css` (theme animations)

## Browser Support

- ✅ Chrome/Edge 88+
- ✅ Firefox 87+
- ✅ Safari 14+
- ✅ iOS Safari 14.4+
- ✅ Android Chrome 88+

## Performance Considerations

1. **FOUC Prevention** - Synchronous inline script ensures instant theme application
2. **Lazy Loading** - theme-manager.js loaded with Alpine
3. **CSS Optimization** - Animations respect prefers-reduced-motion
4. **Database** - Single column update, cached user model
5. **localStorage** - Minimal overhead, fallback caching

## Testing

### Unit Tests
```php
// ThemeService tests
$themeService = app(ThemeService::class);
$this->assertTrue($themeService->isValidTheme('dark'));
```

### Browser Testing
- Test theme cycling with Ctrl+Shift+T
- Test keyboard navigation through theme options
- Test with reduced motion preferences
- Test system preference detection
- Test persistence across page reloads

## Troubleshooting

### Theme not persisting
1. Check database `theme` column exists
2. Verify user model has `theme` in fillable
3. Check localStorage isn't blocked

### Animations not working
1. Check `prefers-reduced-motion` setting
2. Verify CSS file is loaded
3. Check browser DevTools for CSS parsing errors

### Keyboard shortcuts not working
1. Verify `config('theme.features.keyboard_shortcuts')` is true
2. Check if another extension conflicts with Ctrl+Shift+T
3. Test in private/incognito mode

## Future Enhancements

- [ ] Theme preview before applying
- [ ] Custom theme creation
- [ ] Theme scheduling (auto-switch at specific times)
- [ ] Per-page theme overrides
- [ ] Theme analytics
- [ ] More granular motion preferences per component

## References

- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [MDN: prefers-color-scheme](https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-color-scheme)
- [MDN: prefers-reduced-motion](https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-reduced-motion)
- [Livewire Documentation](https://livewire.laravel.com/)
- [Alpine.js Documentation](https://alpinejs.dev/)
