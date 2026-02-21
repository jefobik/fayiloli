<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Custom Role model with UUID primary key.
 *
 * Spatie's default Role model uses auto-incrementing BigInteger IDs.
 * The tenant permission tables use UUID primary keys (consistent with the
 * tenant users table) so this model overrides the PK type accordingly.
 *
 * Registered in config/permission.php under models.role.
 */
class Role extends SpatieRole
{
    use HasUuids;
}
