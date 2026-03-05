# PostgreSQL UUID Validation Error (SQLSTATE[22P02]) - Fix & Best Practices

## Problem

**Error**: `SQLSTATE[22P02]: Invalid text representation: 7 ERROR: invalid input syntax for type uuid: "admin"`

This error occurs when a non-UUID value (like "admin") is passed as a route parameter to a model with a UUID primary key, and the application attempts to query the database with this invalid value.

## Root Cause

PostgreSQL's `uuid` data type requires values to match the UUID format (e.g., `123e4567-e89b-12d3-a456-426614174000`). When Laravel's route model binding receives a non-UUID string and attempts to query with it, PostgreSQL rejects the value at the type-casting level, causing a 22P02 error.

### When This Happens

1. A route defines a parameter with implicit model binding:
   ```php
   Route::get('/tenants/{tenant}', ...);  // expects UUID
   ```

2. A request arrives with a non-UUID value:
   ```
   GET /tenants/admin  // "admin" is not a valid UUID
   ```

3. Laravel's route model binding attempts:
   ```php
   Tenant::where('id', 'admin')->first()
   ```

4. PostgreSQL receives the query and throws 22P02 because `'admin'` cannot be cast to UUID type.

## Solution

### Two-Layer Defense

The fix implements **two protective layers** that work together:

#### Layer 1: `resolveRouteBinding()` - Fast Return
Validates the value immediately and returns `null` if it's not a UUID, preventing further processing.

#### Layer 2: `resolveRouteBindingQuery()` - Database Protection
If Layer 1 somehow fails to catch it, this prevents the query from ever reaching the database by returning `whereRaw('1 = 0')`, which always returns no results.

### Implementation

#### Option 1: Use the Provided Trait (Recommended)

For any UUID-based model, import and use the trait:

```php
<?php

namespace App\Models;

use App\Traits\ProtectsUuidRouteBindings;
use Illuminate\Database\Eloquent\Model;

class MyModel extends Model
{
    use ProtectsUuidRouteBindings;
    
    // Rest of your model code...
}
```

#### Option 2: Direct Implementation

If you prefer not to use a trait, add these methods directly to your model:

```php
public function resolveRouteBinding($value, $field = null)
{
    if ($field === null && !\Illuminate\Support\Str::isUuid($value)) {
        return null;
    }
    return parent::resolveRouteBinding($value, $field);
}

public function resolveRouteBindingQuery($query = null, $value, $field = null)
{
    if (is_null($query)) {
        $query = $this->newQuery();
    }
    if (is_null($field) && !\Illuminate\Support\Str::isUuid($value)) {
        return $query->whereRaw('1 = 0');
    }
    return parent::resolveRouteBindingQuery($query, $value, $field);
}
```

## Which Models Need This

Any Eloquent model that:
- Uses UUID as the primary key
- Is used in route model binding (e.g., `Route::get('/users/{user}', ...)`)
- Might receive non-UUID values in route parameters

Common models in your application:
- ✅ `User` - Already fixed
- ✅ `Tenant` - Already fixed  
- ❓ Any custom models with UUID primary keys

## Better Alternative: Schema-Level Validation

While the above fixes prevent the error at the application level, PostgreSQL also supports enum-like constraints. However, the trait-based approach is preferred because it:

1. **Prevents database round-trips** - Validation happens before the query
2. **Better error handling** - Triggers Laravel's implicit 404 route model binding failure (user-friendly)
3. **Reusable** - Single trait applies to all models
4. **No database migration needed** - No schema changes required

## Testing

To verify the fix works:

```bash
# Before the fix - would throw SQLSTATE[22P02]
curl http://localhost:8000/tenants/admin

# After the fix - returns 404 (model not found)
curl http://localhost:8000/tenants/admin
# HTTP 404 Not Found
```

## Additional Safeguards

### 1. Controller-Level Validation (Extra Safety)

For critical routes, add explicit validation:

```php
public function show(Request $request, string $tenantId)
{
    if (!Str::isUuid($tenantId)) {
        return response('Invalid workspace identifier.', 400);
    }
    
    $tenant = Tenant::findOrFail($tenantId);
    // ...
}
```

### 2. Middleware for Tenant Routes

Create middleware to validate tenant IDs before route model binding:

```php
// app/Http/Middleware/ValidateTenantUuid.php
public function handle($request, $next)
{
    $tenantId = $request->route('tenant')?->getKey() 
               ?? $request->route('tenantId');
    
    if ($tenantId && !Str::isUuid($tenantId)) {
        abort(404, 'Invalid workspace identifier.');
    }
    
    return $next($request);
}
```

Register in `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web->append(ValidateTenantUuid::class);
})
```

### 3. Custom Route Model Binding (Advanced)

For complex scenarios, use explicit binding:

```php
Route::bind('tenant', function ($value) {
    if (!Str::isUuid($value)) {
        throw new ModelNotFoundException();
    }
    return Tenant::findOrFail($value);
});
```

## Debugging Tips

### If You Still See the Error

1. **Check model inheritance** - Ensure the model extends the base Eloquent `Model`
2. **Verify trait usage** - Make sure `ProtectsUuidRouteBindings` is imported
3. **Direct queries** - If error occurs outside route binding, find where the query happens:
   ```bash
   grep -r "->where('id'" app/
   # Add Str::isUuid() check before executing
   ```

4. **Inspect middleware** - Check if middleware bypasses route model binding:
   ```php
   // Log in WhoopsHandler or custom middleware
   if ($exception instanceof QueryException && $exception->errorInfo[0] === '22P02') {
       Log::error('UUID validation error detected', $exception->trace);
   }
   ```

### Enable Query Logging

```php
// In providers or locally
DB::listen(function (QueryExecuted $query) {
    if (str_contains($query->sql, 'id')) {
        \Log::debug('Query:', $query->toSql());
    }
});
```

## Files Modified

- `app/Models/Tenant.php` - Added `resolveRouteBindingQuery()` method
- `app/Models/User.php` - Added `resolveRouteBindingQuery()` method  
- `app/Traits/ProtectsUuidRouteBindings.php` - NEW - Reusable trait

## Performance Impact

**Negligible** - The fix adds one string validation check (`Str::isUuid()`) before the query, which is:
- O(1) regex match
- Executes in microseconds
- Prevents a full database round-trip in error cases

## References

- [PostgreSQL UUID Type](https://www.postgresql.org/docs/current/datatype-uuid.html)
- [Laravel Route Model Binding](https://laravel.com/docs/routing#route-model-binding)
- [Laravel String Helpers](https://laravel.com/docs/helpers#method-str-is-uuid)
