# PostgreSQL UUID Error Fix - Quick Reference

## The Problem
**Error:** `SQLSTATE[22P02]: invalid input syntax for type uuid: "admin"`  
**Cause:** Spatie Permissions wasn't converting role names to UUIDs before database queries

## The Solution Summary

### 1️⃣ Core Fix: User::scopeRole() Method
```php
// app/Models/User.php
public function scopeRole(Builder $query, string|array $roles = [], string $guard = null): Builder
{
    // Resolve 'admin' (name) to actual UUID before pivot query
    $roleIds = Role::where('guard_name', $roleGuard)
        ->whereIn('name', $rolesParam)
        ->pluck('id')
        ->toArray();

    if (empty($roleIds)) {
        return $query->whereRaw('1 = 0');
    }

    return $query->whereHas('roles', function (Builder $q) use ($roleIds) {
        $q->whereIn('roles.id', $roleIds);  // Uses UUID, not role name!
    });
}
```

### 2️⃣ Route Protection: ProtectsUuidRouteBindings Trait
Applied to all UUID-based models:
- Document, Folder, Tag, ShareDocument
- Role, Permission, Category, FileRequest

Provides automatic UUID validation before database queries.

### 3️⃣ User & Tenant Models
Both have `resolveRouteBindingQuery()` that validates UUIDs before executing WHERE clauses.

## Files Changed
- ✅ `app/Models/User.php` - Added `scopeRole()` method
- ✅ `app/Models/Role.php` - Added trait
- ✅ `app/Models/Permission.php` - Added trait
- ✅ `app/Models/Category.php` - Added trait
- ✅ `app/Models/FileRequest.php` - Added trait
- ✅ `app/Traits/ProtectsUuidRouteBindings.php` - NEW
- ✅ `tests/Feature/UserRoleUuidQueryTest.php` - NEW

## How It Works

```
BEFORE (ERROR):
  User::role('admin') 
  → Spatie tries pivot query
  → Passes role name 'admin' directly
  → PostgreSQL: "WHERE users.id IN (admin)" ❌ SQLSTATE[22P02]

AFTER (FIXED):
  User::role('admin')
  → Custom scopeRole() intercepts
  → Converts 'admin' → UUID 'e29b-41d4-...'
  → PostgreSQL: "WHERE roles.id IN ('e29b-41d4-...')" ✅ Works!
```

## Testing
```bash
php artisan test tests/Feature/UserRoleUuidQueryTest.php
php artisan test tests/Feature/UuidRouteBindingProtectionTest.php
```

## Verification
After deployment, these should work without errors:
```php
// Home dashboard loads
User::role('admin')->get();  // No SQLSTATE[22P02] error!

// Role management
Role::findOrFail($roleId);   // UUID validated at route level

// Document operations
Document::find($docId);      // UUID validated at model level
```
