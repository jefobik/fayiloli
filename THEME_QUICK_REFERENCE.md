# Theme System - Quick Reference Guide

## For Developers

### Quick Start

#### Display Theme Switcher in Your View
```blade
<livewire:global-theme-switcher />
```

#### Get User's Theme Preference
```php
$theme = app(\App\Services\ThemeService::class)->getThemePreference();
// Returns: 'light', 'dark', or 'system'
```

#### Set User's Theme
```php
app(\App\Services\ThemeService::class)->setThemePreference('dark');
```

#### Generate Bootstrap Script (Prevent FOUC)
```blade
@php
    $themeService = app(\App\Services\ThemeService::class);
    $theme = $themeService->getThemePreference();
@endphp

{!! $themeService->generateThemeBootstrapScript($theme) !!}
```

---

## API Reference

### ThemeService Class

#### Methods

**`getThemePreference(): string`**
- Returns user's current theme preference
- Returns: `'light'`, `'dark'`, or `'system'`
- Default: `'system'` if user not authenticated

**`setThemePreference(string $theme): void`**
- Set and persist user's theme preference
- Throws: `InvalidArgumentException` if theme is invalid
- Persists to: User model, localStorage, session

**`getResolvedTheme(): string`**
- Get the actual applied theme (not preference setting)
- Returns: `'light'` or `'dark'` (never `'system'`)

**`isValidTheme(string $theme): bool`**
- Check if a theme string is valid
- Returns: `true` if valid, `false` otherwise

**`getAvailableThemes(): array`**
- Get list of available theme options
- Returns: `['light', 'dark', 'system']`

**`getThemeLabel(string $theme): string`**
- Get human-readable label for a theme
- Returns: `'Light'`, `'Dark'`, `'System'`, or `'Unknown'`

**`getThemeIcon(string $theme): string`**
- Get Font Awesome icon class for theme
- Returns: `'fas fa-sun'`, `'fas fa-moon'`, `'fas fa-desktop'`

**`generateThemeBootstrapScript(string $theme, bool $includeScriptTags = true): string`**
- Generate inline script to prevent FOUC (Flash of Unstyled Content)
- Must be placed in `<head>` before CSS
- Returns: JavaScript code that applies theme immediately

**`getHtmlAttributes(): array`**
- Get recommended data attributes for `<html>` element
- Returns: Array with `data-theme`, `data-theme-preference`, and CSS classes

---

## CSS Class Reference

### Dark Mode Indicators

#### On Document Root
```html
<html class="dark dark-mode" data-theme="dark">
```

#### On Body
```html
<body class="dark-mode">
```

#### Data Attributes
```html
<html data-theme="system|light|dark"
      data-theme-preference="system|light|dark"
      data-bs-theme="light|dark"
      data-current-theme="light|dark">
```

### Styling Dark Mode

```css
/* Light mode (default) */
.my-element {
    background: white;
    color: black;
}

/* Dark mode */
html.dark .my-element,
body.dark-mode .my-element {
    background: #1f2937;
    color: white;
}

/* Bootstrap dark mode */
[data-bs-theme="dark"] .my-element {
    background: #1f2937;
}

/* Tailwind dark mode class */
.dark .my-element {
    background: #1f2937;
}
```

---

## JavaScript API

### Events

#### Listening for Theme Changes
```javascript
window.addEventListener('theme-applied', (event) => {
    const { theme, isDark, timestamp } = event.detail;
    console.log(`Theme applied: ${theme}, Dark: ${isDark}`);
});
```

#### Livewire Events
```javascript
// Listen in Livewire component
$wire.on('theme-updated', (data) => {
    console.log('Theme updated to:', data.theme);
});
```

### Custom Events

#### Dispatch Theme Cycle
```javascript
window.dispatchEvent(new CustomEvent('cycle-theme', {
    detail: { nextTheme: 'dark' }
}));
```

#### Keyboard Shortcut (Ctrl+Shift+T)
Automatically handled by theme-manager.js
Cycles: system → light → dark → system

---

## Configuration

### config/theme.php

```php
return [
    // Available theme options
    'available' => ['light', 'dark', 'system'],
    
    // Default theme
    'default' => 'system',
    
    // Storage locations
    'storage' => [
        'user_model_field' => 'theme',
        'local_storage_key' => 'theme_preference',
        'session_key' => 'user_theme',
    ],
    
    // Theme metadata
    'themes' => [
        'light' => [
            'label' => 'Light',
            'icon' => 'fas fa-sun',
        ],
        'dark' => [
            'label' => 'Dark',
            'icon' => 'fas fa-moon',
        ],
        'system' => [
            'label' => 'System',
            'icon' => 'fas fa-desktop',
        ],
    ],
    
    // Transition settings
    'transitions' => [
        'enabled' => true,
        'duration' => 150,  // milliseconds
        'easing' => 'ease-in-out',
        'respect_prefers_reduced_motion' => true,
    ],
    
    // CSS classes
    'css' => [
        'dark_class' => 'dark',
        'dark_mode_class' => 'dark-mode',
        'transition_class' => 'theme-transition',
    ],
    
    // Feature flags
    'features' => [
        'keyboard_shortcuts' => true,
        'system_preference_detection' => true,
        'local_storage_sync' => true,
        'persist_to_database' => true,
        'smooth_transitions' => true,
    ],
];
```

---

## Common Patterns

### Check if Dark Mode is Active

#### JavaScript
```javascript
const isDark = document.documentElement.classList.contains('dark');
```

#### PHP/Blade
```php
$isDark = app(\App\Services\ThemeService::class)->getResolvedTheme() === 'dark';
```

#### CSS
```css
@media (prefers-color-scheme: dark) {
    /* Styles for dark mode */
}

/* Or when user explicitly sets dark */
html.dark .element,
body.dark-mode .element {
    /* Styles */
}
```

### Conditional Rendering

```blade
@php
    $isDark = app(\App\Services\ThemeService::class)->getResolvedTheme() === 'dark';
@endphp

@if($isDark)
    <!-- Dark mode content -->
@else
    <!-- Light mode content -->
@endif
```

### Tailwind Usage

```blade
<div class="bg-white dark:bg-slate-900 text-black dark:text-white">
    Content adapts to theme
</div>
```

---

## Testing

### Unit Test Example
```php
public function test_theme_service()
{
    $service = app(\App\Services\ThemeService::class);
    
    $this->assertTrue($service->isValidTheme('dark'));
    $this->assertFalse($service->isValidTheme('invalid'));
    
    $service->setThemePreference('light');
    $this->assertEquals('light', $service->getThemePreference());
}
```

### Feature Test Example
```php
public function test_user_can_switch_theme()
{
    $response = $this->actingAs($user)
                     ->livewire(GlobalThemeSwitcher::class)
                     ->call('updateTheme', 'dark');
    
    $this->assertEquals('dark', $user->fresh()->theme);
}
```

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Theme not persisting | Verify `users.theme` column exists |
| Animations jumpy | Check if `prefers-reduced-motion` is enabled |
| Dark mode not applying | Clear browser cache and localStorage |
| Keyboard shortcut conflicts | Check browser extensions or remapping |
| FOUC on page load | Ensure bootstrap script is in `<head>` |
| Screen reader not announcing theme | Check ARIA attributes are present |

---

## Files to Know

| File | Purpose |
|------|---------|
| `app/Services/ThemeService.php` | Core theme logic |
| `config/theme.php` | Configuration settings |
| `resources/js/theme-manager.js` | Client-side theme management |
| `resources/css/app.css` | Theme animations (search for "THEME TRANSITION") |
| `app/Livewire/GlobalThemeSwitcher.php` | Theme switcher component |
| `resources/views/livewire/global-theme-switcher.blade.php` | Theme switcher view |
| `docs/THEME_SWITCHER.md` | Full documentation |
| `tests/Unit/ThemeServiceTest.php` | Unit tests |

---

## Performance Tips

1. **Cache the ThemeService singleton**
   - Already registered in AppServiceProvider
   - Use dependency injection

2. **Use CSS custom properties for dynamic colors**
   ```css
   :root {
       --bg-primary: #ffffff;
       --text-primary: #000000;
   }
   
   html.dark {
       --bg-primary: #1f2937;
       --text-primary: #f3f4f6;
   }
   ```

3. **Preload theme preference in server response**
   - Prevents hydration mismatch
   - Reduces layout shift

4. **Use CSS hardware acceleration**
   ```css
   .element {
       will-change: color, background-color;
       transform: translateZ(0);  /* Enable GPU acceleration */
   }
   ```

---

## Security Considerations

1. ✅ Theme value validated in service
2. ✅ Only authentic users can modify preferences
3. ✅ No sensitive data in theme
4. ✅ localStorage is domain-scoped (same-origin policy)
5. ✅ No XSS vectors in theme values

---

## Accessibility Checklist

Before shipping theme-dependent features:

- [ ] Test with keyboard navigation only
- [ ] Test with screen reader (NVDA, JAWS)
- [ ] Verify WCAG AA color contrast
- [ ] Test with `prefers-reduced-motion` enabled
- [ ] Test with Firefox and Chrome
- [ ] Verify focus indicators are visible
- [ ] Check ARIA labels are present
- [ ] Test system preference detection

---

For more details, see [THEME_SWITCHER.md](docs/THEME_SWITCHER.md) or [THEME_REFACTORING_SUMMARY.md](THEME_REFACTORING_SUMMARY.md)
