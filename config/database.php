<?php

/*
|--------------------------------------------------------------------------
| Hybrid Database Architecture — EDMS (Fayiloli)
|--------------------------------------------------------------------------
|
| PRIMARY   → PostgreSQL  (pgsql)   : structured relational data
|             Users, Folders, Documents, Tags, Categories, ShareDocuments,
|             FileRequests, Roles/Permissions (Spatie), Tenants, Sessions,
|             Jobs, Cache, Migrations.
|
| SECONDARY → MongoDB     (mongodb) : unstructured / high-volume data
|             Notifications, ActivityLogs, DocumentAnalytics.
|             Uses the official `mongodb/laravel-mongodb` driver.
|
| Each connection uses its own namespaced env-var prefix (PGSQL_* /
| MONGODB_*) so the two databases cannot accidentally share credentials.
| The legacy DB_* variables still resolve for the pgsql connection to
| stay compatible with existing tooling (Artisan, Tinker, CI pipelines).
|
*/

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    */

    'default' => env('DB_CONNECTION', 'pgsql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    */

    'connections' => [

        // ── Primary: PostgreSQL ──────────────────────────────────────────────
        // Stores all relational, transactional data.
        // Env vars: PGSQL_* (preferred) with DB_* as backward-compat fallbacks.

        'pgsql' => [
            'driver'         => 'pgsql',
            'url'            => env('DATABASE_URL'),               // Heroku-style full DSN (optional)
            'host'           => env('PGSQL_HOST', env('DB_HOST', '127.0.0.1')),
            'port'           => env('PGSQL_PORT', env('DB_PORT', '5432')),
            'database'       => env('PGSQL_DATABASE', env('DB_DATABASE', 'ict_edms')),
            'username'       => env('PGSQL_USERNAME', env('DB_USERNAME', 'postgres')),
            'password'       => env('PGSQL_PASSWORD', env('DB_PASSWORD', '')), // never hard-code a default password
            'charset'        => 'utf8',
            'prefix'         => '',
            'prefix_indexes' => true,
            'search_path'    => env('PGSQL_SCHEMA', 'public'),
            'sslmode'        => env('PGSQL_SSLMODE', 'prefer'),
        ],

        // ── Secondary: MongoDB ───────────────────────────────────────────────
        // Stores high-volume, schema-flexible data (notifications, audit logs,
        // document analytics).  Requires `mongodb/laravel-mongodb` package.
        // Uses DSN-style config so authentication works across all MongoDB
        // deployment types (standalone, replica set, Atlas, sharded cluster).

        // ── Secondary: MongoDB ───────────────────────────────────────────────
        // Stores high-volume, schema-flexible data (notifications, audit logs,
        // document analytics).  Requires `mongodb/laravel-mongodb` package.
        //
        // Auth credentials MUST be embedded in MONGODB_URI — the v5.x driver
        // uses the DSN directly and does not merge separate username/password
        // keys.  Format: mongodb://user:pass@host:port/db?authSource=db
        //
        // The `edms_app` user has readWrite on `edms_nosql` only.

        'mongodb' => [
            'driver'   => 'mongodb',
            'dsn'      => env('MONGODB_URI', 'mongodb://127.0.0.1:27017'),
            'database' => env('MONGODB_DATABASE', 'edms_nosql'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | Tracks completed migrations.  MongoDB collections are schema-less and
    | do not require traditional migrations — only pgsql migrations run here.
    |
    */

    'migrations' => [
        'table'               => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Used for SESSION_DRIVER, CACHE_STORE, and QUEUE_CONNECTION when
    | configured. Separate databases (0 = default, 1 = cache) to avoid
    | key collisions.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix'  => env(
                'REDIS_PREFIX',
                Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_'
            ),
        ],

        'default' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
