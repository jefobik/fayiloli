<?php

/*
|--------------------------------------------------------------------------
| Spatie Laravel Activitylog — Hybrid Architecture Config
|--------------------------------------------------------------------------
|
| Activity records are routed to MongoDB (connection: mongodb) so that
| high-volume audit data does not bloat the primary PostgreSQL database.
| The custom App\Models\MongoActivity model implements Spatie's
| ActivityContract, so all package helpers work transparently.
|
*/

return [

    /*
    | If set to false, no activities will be saved at all.
    */
    'enabled' => env('ACTIVITY_LOGGER_ENABLED', true),

    /*
    | Activity records older than this many days are pruned by
    | `php artisan activitylog:clean`.
    */
    'delete_records_older_than_days' => 365,

    /*
    | Default log name when none is passed to the activity() helper.
    */
    'default_log_name' => 'default',

    /*
    | Auth driver used to resolve the causer.
    | null = use the default Laravel auth driver.
    */
    'default_auth_driver' => null,

    /*
    | When true, querying the subject relation will include soft-deleted
    | records from the PostgreSQL side.
    */
    'subject_returns_soft_deleted_models' => false,

    /*
    | MongoDB-backed activity model.
    | Must implement Spatie\Activitylog\Contracts\Activity.
    */
    'activity_model' => \App\Models\MongoActivity::class,

    /*
    | Table / collection name used for activity records.
    | For MongoDB this is the collection name.
    */
    'table_name' => env('ACTIVITY_LOGGER_TABLE_NAME', 'activity_log'),

    /*
    | Secondary database connection for activity logs.
    | Routed to MongoDB so audit data stays separate from PostgreSQL.
    */
    'database_connection' => env('ACTIVITY_LOGGER_DB_CONNECTION', 'mongodb'),

];
