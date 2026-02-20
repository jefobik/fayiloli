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
            'database'       => env('PGSQL_DATABASE', env('DB_DATABASE', 'fayiloli_central')),
            'username'       => env('PGSQL_USERNAME', env('DB_USERNAME', 'edms_user')),
            'password'       => env('PGSQL_PASSWORD', env('DB_PASSWORD', '')), // never hard-code a default password
            'charset'        => 'utf8',
            'prefix'         => '',
            'prefix_indexes' => true,
            'search_path'    => env('PGSQL_SCHEMA', 'public'),
            'sslmode'        => env('PGSQL_SSLMODE', 'prefer'),
        ],

        // ── Secondary: MongoDB ───────────────────────────────────────────────
        // Stores high-volume, schema-flexible data (notifications, audit logs,
        // document analytics).  Requires `mongodb/laravel-mongodb` ^5.6.
        //
        // Uses individual config keys (NOT a raw DSN) so that authSource can
        // be set independently of the target database.  This is necessary
        // because the `edms_app` MongoDB user was created in the `edms_nosql`
        // administrative database (authSource), while the application writes
        // to the `fayiloli_edms` database.  Embedding both in a single URI
        // string conflates the two concerns and makes one wrong.
        //
        // When the `dsn` key is absent, laravel-mongodb v5.x builds the
        // connection URI from the individual keys below.
        //
        // Env vars:
        //   MONGODB_HOST          — default 127.0.0.1
        //   MONGODB_PORT          — default 27017
        //   MONGODB_USERNAME      — app user (edms_app)
        //   MONGODB_PASSWORD      — app user password
        //   MONGODB_DATABASE      — target database (fayiloli_edms)
        //   MONGODB_AUTH_SOURCE   — database the user was created in (edms_nosql)
        //
        // MONGODB_URI is intentionally NOT used here; keep it in .env only as
        // a human-readable reference / for external tooling (Compass, etc.).

        'mongodb' => [
            'driver'   => 'mongodb',
            'host'     => env('MONGODB_HOST', '127.0.0.1'),
            'port'     => (int) env('MONGODB_PORT', 27017),
            'database' => env('MONGODB_DATABASE', 'fayiloli_edms'),
            'username' => env('MONGODB_USERNAME', 'edms_app'),
            'password' => env('MONGODB_PASSWORD'),
            'options'  => [
                // The database in which the user was created — distinct from
                // the target database above.  SCRAM-SHA-256 is the default
                // negotiated mechanism for MongoDB 4.0+; explicit is clearer.
                'authSource'     => env('MONGODB_AUTH_SOURCE', 'edms_nosql'),
                'authMechanism'  => 'SCRAM-SHA-256',
            ],
        ],

        // ── Admin: PostgreSQL superuser (setup / privilege grants only) ──────
        // Used exclusively by the `db:grant-privileges` Artisan command.
        // Set DB_ADMIN_USERNAME / DB_ADMIN_PASSWORD in .env, run the command
        // once, then you may remove the variables.

        'pgsql_admin' => [
            'driver'         => 'pgsql',
            'host'           => env('PGSQL_HOST', env('DB_HOST', '127.0.0.1')),
            'port'           => env('PGSQL_PORT', env('DB_PORT', '5432')),
            'database'       => env('PGSQL_DATABASE', env('DB_DATABASE', 'fayiloli_central')),
            'username'       => env('DB_ADMIN_USERNAME', 'postgres'),
            'password'       => env('DB_ADMIN_PASSWORD', ''),
            'charset'        => 'utf8',
            'prefix'         => '',
            'prefix_indexes' => true,
            'search_path'    => 'public',
            'sslmode'        => env('PGSQL_SSLMODE', 'prefer'),
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
