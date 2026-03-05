# UUID Validation Error Fix - Quick Reference

## TL;DR

**Problem**: `SQLSTATE[22P02]: invalid input syntax for type uuid: "admin"`  
**Cause**: Non-UUID values passed to route parameters with UUID models  
**Solution**: Use `ProtectsUuidRouteBindings` trait on UUID-based models

## One-Line Fix

Add `use ProtectsUuidRouteBindings;` to any Eloquent model with UUID primary key:

```php
class MyModel extends Model
{
    use ProtectsUuidRouteBindings;  // ← Add this line
}
```

## How It Works

| Layer | Method | Purpose | Result |
|-------|--------|---------|--------|
| 1 | `resolveRouteBinding()` | Immediate validation | Returns `null` for non-UUIDs |
| 2 | `resolveRouteBindingQuery()` | Database protection | Blocks query if Layer 1 fails |

## What Triggers the Error

```
❌ GET /tenants/admin                    # "admin" is not a UUID
❌ Get /users/superadmin?...             # "superadmin" is not a UUID
❌ POST /folders/list?tenant=test        # "test" is not a UUID

✅ GET /tenants/123e4567-e89b-12d3...   # Valid UUID format
```

## Models Already Protected

- ✅ `Tenant`
- ✅ `User`

## Models That Might Need Protection

Search for:
- Laravel relationship routes with UUID models
- Custom models used in route binding
- Admin panels handling UUID resources

## Testing

```bash
# Before fix (would error)
php artisan tinker
>>> auth()->loginUsingId('admin')  # SQLSTATE[22P02]

# After fix (returns null safely)
>>> auth()->loginUsingId('admin')  # null, no error
```

## Adding to Other Models

For any model that needs UUID protection:

```php
namespace App\Models;

use App\Traits\ProtectsUuidRouteBindings;

class CustomResource extends Model
{
    use ProtectsUuidRouteBindings;
    // ... rest of model
}
```

## Common Error Messages

| Error | Cause | Fix |
|-------|-------|-----|
| `SQLSTATE[22P02]` | Non-UUID in route param | Add trait to model |
| `Model not found` | Route binding returned 404 | Expected behavior ✓ |
| `Column not found` | Wrong column validation | Check `getRouteKeyName()` |

## Debugging

```php
// Check if a value is valid UUID
use Illuminate\Support\Str;

Str::isUuid('admin')        // false
Str::isUuid('123e4567...')  // true
```

## File Locations

| File | Purpose |
|------|---------|
| `app/Traits/ProtectsUuidRouteBindings.php` | Reusable trait |
| `UUID_VALIDATION_FIX.md` | Full documentation |
| `tests/Feature/UuidRouteBindingProtectionTest.php` | Test suite |

## Need Help?

1. **Check existing code** - See `Tenant.php` and `User.php` for examples
2. **Read full guide** - See `UUID_VALIDATION_FIX.md`
3. **Run tests** - `php artisan test tests/Feature/UuidRouteBindingProtectionTest.php`
4. **Enable query logging** - See UUID_VALIDATION_FIX.md debugging section

## Performance

- ✅ No noticeable impact
- ✅ Microsecond regex validation
- ✅ Prevents expensive DB queries
- ✅ Scales to any number of models
