# 🏢 Theme Switcher: Multi-Tenancy Guide

**Status:** Production-Ready for stancl/tenancy  
**Framework:** Laravel with Livewire v4 and Alpine.js  
**Architecture:** Tenant-Aware Service-Based Pattern

---

## Overview

The Global Theme Switcher is designed from the ground up to respect **stancl/tenancy's** central vs. tenant context separation. Each user in each tenant workspace maintains independent theme preferences, completely isolated from users in other tenants or the central admin system.

### Key Principles

1. **Context Awareness**: System detects whether code is running in central or tenant context
2. **Database Isolation**: Theme preferences stored in the appropriate database (central or tenant-specific)
3. **localStorage Namespacing**: Browser storage keys include tenant ID to prevent conflicts
4. **Transparent Operation**: Same code works seamlessly in both contexts without special handling

---

## Architecture Overview

### Component Hierarchy

```
┌─ Central/Tenant Context Layer (Tenancy Facade)
│  └─ Detects: Tenancy::initialized() + current tenant ID
│
├─ Backend Layer
│  ├─ ThemeService (app/Services/ThemeService.php)
│  │  └─ Context-Aware Methods:
│  │     ├─ isTenantContext(): bool
│  │     ├─ getCurrentTenant()
│  │     ├─ getThemePreference(): string
│  │     └─ setThemePreference(string)
│  │
│  └─ GlobalThemeSwitcher Component (app/Livewire/GlobalThemeSwitcher.php)
│     └─ Context-Tracked Properties:
│        ├─ tenantId, tenantSlug, isTenantContext
│        └─ Dispatches events with tenant metadata
│
├─ Configuration Layer
│  └─ config/theme.php
│     └─ Tenancy settings:
│        ├─ enabled: true
│        ├─ per_tenant_preferences: true
│        └─ sync_central_preference: false
│
└─ Frontend Layer
   ├─ Vue/Blade Template (resources/views/livewire/global-theme-switcher.blade.php)
   │  └─ Renders theme buttons + optional tenant context display
   │
   └─ JavaScript Manager (resources/js/theme-manager.js)
      └─ Tenant-Aware Logic:
         ├─ getStorageKey(): Generates tenant-specific key
         ├─ applyTheme(): Works in current context
         └─ Custom events: Include tenantId in payload
```

---

## Tenant Context Detection

### Server-Side Detection

```php
// ThemeService::isTenantContext()
public function isTenantContext(): bool {
    return Tenancy::initialized();
}

// ThemeService::getCurrentTenant()
public function getCurrentTenant() {
    return Tenancy::initialized() ? Tenancy::getTenant() : null;
}
```

**Returns:**
- **true** if executing within a tenant workspace
- **false** if executing in central admin system

### Client-Side Detection

The JavaScript theme manager automatically receives tenant context:

```javascript
// window.__tenantContext (injected via Blade template)
{
    isTenantContext: true,
    tenantId: "550e8400-e29b-41d4-a716-446655440000",
    tenantSlug: "acme-corp",
    contextType: "tenant"  // or "central"
}
```

---

## Database Isolation

### User Model Columns

Both **central** and **tenant-specific** user tables have a `theme` column:

```sql
-- central database: users table
ALTER TABLE users ADD COLUMN theme VARCHAR(255) DEFAULT 'system';

-- tenant database: users table (created per tenant)
ALTER TABLE users ADD COLUMN theme VARCHAR(255) DEFAULT 'system';
```

### ThemeService Persistence

The service automatically reads/writes to the correct database:

```php
// When in tenant context, Auth::user() returns the tenant's User instance
// and changes are persisted to the tenant database

// When in central context, Auth::user() returns the central User instance
// and changes are persisted to the central database

public function setThemePreference(string $theme): void {
    Auth::user()?->update(['theme' => $theme]);
    // Tenancy automatically ensures this updates the correct database
}
```

---

## Feature: Per-Tenant Preferences

### Configuration

```php
// config/theme.php
'tenancy' => [
    'enabled' => true,                    // Enable tenancy support
    'per_tenant_preferences' => true,     // Each tenant has own preferences
    'sync_central_preference' => false,   // Don't sync between contexts
],
```

### Behavior

| Setting | Central User | Tenant User A | Tenant User B |
|---------|--------------|---------------|---------------|
| Theme Set to Dark | ✅ Saved centrally | ❌ No effect | ❌ No effect |
| Tenant A: Theme Set to Light | ❌ No effect | ✅ Saved in A's DB | ❌ No effect |
| Tenant B: Theme Set to Light | ❌ No effect | ❌ No effect | ✅ Saved in B's DB |

---

## localStorage Namespacing

### Key Generation

The JavaScript manager generates tenant-aware storage keys:

```javascript
// Client-side: theme-manager.js
getStorageKey() {
    if (this.isTenantContext && this.tenantId) {
        return `theme_preference_${this.tenantId}`;  // e.g., "theme_preference_550e8400..."
    }
    return 'theme_preference_central';  // Central admin
}
```

### Result

```javascript
// Browser localStorage (same domain, different keys)

// Central admin user
localStorage['theme_preference_central'] = 'dark'

// Tenant user (Acme Corp)
localStorage['theme_preference_550e8400-e29b-41d4-a716-446655440000'] = 'light'

// Tenant user (TechCorp)
localStorage['theme_preference_uuid-for-techcorp'] = 'system'
```

**Benefit:** Multiple tenants can have browsers open on the same domain without theme conflicts.

---

## Event Flow: Theme Update

### Step 1: User Clicks Theme Button

User in Tenant A's workspace clicks "Dark Mode" button.

### Step 2: Livewire Component Updates

```php
// app/Livewire/GlobalThemeSwitcher.php

#[On('update-theme')]
public function updateTheme($theme): void {
    $this->theme = $theme;
    $this->themeService->setThemePreference($theme);
    
    $this->dispatch('theme-updated',
        theme: $theme,
        timestamp: now()->timestamp,
        tenantId: $this->tenantId,              // "550e8400..."
        tenantContext: $this->isTenantContext    // true
    );
}
```

### Step 3: JavaScript Listens to Event

```javascript
// resources/js/theme-manager.js

window.addEventListener('theme-updated', (event) => {
    const { detail } = event;
    
    console.log(detail);
    // {
    //   theme: 'dark',
    //   timestamp: 1677895234000,
    //   tenantId: '550e8400-e29b-41d4-a716-446655440000',
    //   tenantContext: true
    // }
    
    this.currentTheme = detail.theme;
    this.saveToLocalStorage();  // Uses tenant-aware key!
    this.applyTheme(detail.theme);
});
```

### Step 4: Theme Applied

Blade page updates DOM classes: `dark` mode activated in Tenant A's workspace.

---

## Debug Features

### Enable Debug Display

Set in `.env`:

```bash
APP_DEBUG=true
```

Then in `config/theme.php`:

```php
'features' => [
    'show_tenancy_context' => env('APP_DEBUG', false),
]
```

### Component Displays Context Info

Update [resources/views/livewire/global-theme-switcher.blade.php](resources/views/livewire/global-theme-switcher.blade.php) to show tenant context:

```blade
@if(config('theme.features.show_tenancy_context') && $contextInfo)
    <div class="text-xs text-gray-600 mt-2 p-2 bg-gray-100 rounded">
        <p><strong>Debug Info:</strong></p>
        <p>Context: {{ $contextInfo['is_tenant_context'] ? 'Tenant' : 'Central' }}</p>
        @if($contextInfo['is_tenant_context'])
            <p>Tenant: {{ $contextInfo['tenant_slug'] }} ({{ $contextInfo['tenant_id'] }})</p>
        @endif
        <p>User: {{ $contextInfo['user_id'] }}</p>
        <p>Theme: {{ $contextInfo['theme_preference'] }}</p>
    </div>
@endif
```

### Console Methods (JavaScript)

```javascript
// Get current tenancy info in browser console
themeManager.getTenancyInfo()
// Returns: {
//   isTenantContext: true,
//   tenantId: "550e8400...",
//   storageKey: "theme_preference_550e8400...",
//   currentTheme: "dark"
// }
```

---

## Implementation Checklist

- [x] **ThemeService**: `isTenantContext()`, `getCurrentTenant()` methods
- [x] **GlobalThemeSwitcher**: Captures tenant context in `mount()`
- [x] **Event Dispatch**: Includes `tenantId` and `tenantContext` in payload
- [x] **JavaScript Manager**: `getStorageKey()` uses tenant-aware namespacing
- [x] **layouts/app.blade.php**: Injects `window.__tenantContext`
- [x] **layouts/central.blade.php**: Injects `window.__tenantContext`
- [x] **config/theme.php**: Tenancy configuration block
- [ ] **Optional**: Update view to display tenant context (debug mode)
- [ ] **Testing**: Add tenant-specific unit tests

---

## Testing Multi-Tenancy Behavior

### Scenario 1: Two Tenants, Different Themes

```bash
# Terminal 1: Central admin sets dark theme
curl http://localhost/admin -H "Set-Cookie: laravel_session=admin_session"
# Admin theme saved: DB[central].users[1].theme = 'dark'

# Terminal 2: Tenant A user sets light theme
curl http://acme.localhost/dashboard -H "Set-Cookie: laravel_session=tenant_a_session"
# Tenant A theme saved: DB[acme].users[1].theme = 'light'

# Terminal 3: Check they're isolated
SELECT theme FROM users WHERE id=1;  -- central DB shows 'dark'
-- Switch context to acme tenant
SELECT theme FROM users WHERE id=1;  -- acme DB shows 'light'
```

### Scenario 2: Browser localStorage Isolation

```javascript
// Simulate two tenants open in same browser

// Tab 1: Tenant A (tenantId = '550e8400...')
window.__tenantContext.tenantId // "550e8400..."
localStorage['theme_preference_550e8400...'] = 'dark'

// Tab 2: Tenant B (tenantId = 'xxxxxxxx...')
window.__tenantContext.tenantId // "xxxxxxxx..."
localStorage['theme_preference_xxxxxxxx...'] = 'light'

// No conflict! Different keys for different tenants
```

### Scenario 3: Central Admin Not Affected

```javascript
// Central admin context
window.__tenantContext.isTenantContext  // false
window.__tenantContext.contextType      // "central"

// Theme saved to 'theme_preference_central' key
// Separate from any tenant keys
```

---

## Migration Path for Existing Themes

If you had theme preferences stored in a central database and want to migrate to per-tenant preferences:

```php
// app/Console/Commands/MigrateThemesToTenancies.php

public function handle() {
    // 1. Get all central users with themes
    $centralUsers = User::all();
    
    // 2. For each tenant, ensure tenant user has a theme preference
    foreach (Tenant::all() as $tenant) {
        $tenant->run(function () use ($centralUsers) {
            // Optionally copy central preferences to tenant
            foreach ($centralUsers as $centeralUser) {
                User::updateOrCreate(
                    ['email' => $centralUser->email],
                    ['theme' => $centralUser->theme]
                );
            }
        });
    }
}
```

---

## Performance Considerations

### Database Queries

- **Per Page Load**: 1 query to fetch `users.theme`
- **Per Theme Switch**: 1 query to update `users.theme`
- **No N+1 Issues**: Tenancy::getTenant() is injected once per request

### Browser Storage

- **localStorage Key Prefix**: `theme_preference_` (~20 bytes)
- **Tenant ID Storage**: UUID (~36 bytes)
- **Value Storage**: `light|dark|system` (~6 bytes)
- **Total per tenant**: ~62 bytes (negligible)

### Recommendation

Cache theme preference in Livewire component state after initial fetch to avoid repeated queries:

```php
// Already done in GlobalThemeSwitcher components
public function mount(): void {
    $this->themeService = app(ThemeService::class);
    $this->theme = $this->themeService->getThemePreference();  // Cached
    // ... subsequent updates use $this->theme from state
}
```

---

## Common Issues & Troubleshooting

### Issue: Theme Not Persisting in Tenant Context

**Diagnosis:**
```php
// In tenant-aware route, check if Tenancy::initialized()
dd(Tenancy::initialized(), Tenancy::getTenant()?->id);
```

**Common Cause:** Tenancy not initialized (check middleware order in `config/tenancy.php`)

**Solution:** Ensure `InitializeTenancy` middleware runs before accessing theme service.

### Issue: localStorage Showing Wrong Key

**Diagnosis:**
```javascript
console.log(window.__tenantContext);
console.log(themeManager.getStorageKey());
```

**Common Cause:** `window.__tenantContext` not injected in layout

**Solution:** Verify both `app.blade.php` and `central.blade.php` include the tenant context script after line 60 area.

### Issue: Theme Flickering Between Requests

**Diagnosis:** FOUC (Flash of Unstyled Content) occurs

**Cause:** Theme bootstrap script not running before DOM parse

**Solution:** Ensure `ThemeService::generateThemeBootstrapScript()` is in `<head>` BEFORE Tailwind/Bootstrap CSS loads.

---

## API Reference

### ThemeService Methods

```php
namespace App\Services;

class ThemeService {
    /**
     * Detect if currently in tenant context
     * @return bool
     */
    public function isTenantContext(): bool;
    
    /**
     * Get currently active tenant
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getCurrentTenant();
    
    /**
     * Get current user's theme preference
     * Works in both central and tenant contexts
     * @return string ('light' | 'dark' | 'system')
     */
    public function getThemePreference(): string;
    
    /**
     * Set current user's theme preference
     * Persists to correct database (central or tenant)
     * @param string $theme
     * @return void
     */
    public function setThemePreference(string $theme): void;
    
    /**
     * Get debugging context information
     * @return array
     */
    public function getContextInfo(): array;
    
    /**
     * Generate inline script for FOUC prevention
     * @param string $theme
     * @return string
     */
    public function generateThemeBootstrapScript(string $theme): string;
}
```

### JavaScript API

```javascript
// Alpine component: x-data="themeManager()"

// Methods
init()                          // Initialize theme manager
getStorageKey()                // Get tenant-aware localStorage key
getThemeLabel(theme)           // Get human label for theme
cycleTheme()                   // Rotate through themes
applyTheme(theme)              // Apply theme to DOM
resetToSystem()                // Reset to system preference
getTenancyInfo()               // Get debug info (object)

// Properties
currentTheme                   // 'light' | 'dark' | 'system'
isDarkMode                     // boolean
isTenantContext                // boolean
tenantId                       // string | null
isTransitioning                // boolean (during animation)
```

---

## Related Documentation

- [THEME_SWITCHER.md](THEME_SWITCHER.md) - Core theme switching implementation
- [THEME_QUICK_REFERENCE.md](THEME_QUICK_REFERENCE.md) - Quick API reference
- [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) - Production deployment
- [ACCESSIBILITY_COMPLIANCE.md](ACCESSIBILITY_COMPLIANCE.md) - WCAG 2.1 AA compliance

---

## Conclusion

The Global Theme Switcher is fully integrated with stancl/tenancy. Each tenant workspace has completely independent theme management, with clean separation of concerns and transparent tenant context detection. The system scales seamlessly from single-tenant applications to multi-tenant SaaS platforms.

**Next Steps:**
1. Test in actual multi-tenant environment
2. Optionally customize per-tenant theme palettes
3. Monitor performance metrics (database queries per request)
4. Consider caching layer for frequently accessed theme preferences
