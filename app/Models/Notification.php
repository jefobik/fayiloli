<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use MongoDB\Laravel\Eloquent\Model as MongoModel;

/**
 * MongoDB-backed Notification model.
 *
 * Stored in the `edms_nosql` MongoDB database (connection: mongodb).
 * Relational references (user_id, model_id) still point to PostgreSQL rows
 * and can be resolved via explicit belongsTo helpers below.
 *
 * @property string      $_id            MongoDB ObjectId
 * @property int|null    $user_id        FK → users.id (PostgreSQL)
 * @property string|null $user_type      Morph type (e.g. App\Models\User)
 * @property string      $activity_type  e.g. UPLOAD, SHARE, DELETE, COMMENT
 * @property string|null $model_type     Subject morph type
 * @property mixed|null  $model_id       Subject morph id
 * @property string      $message        Human-readable notification text
 * @property string      $status         UNREAD | READ
 * @property string      $dismiss_status PENDING | DISMISSED
 * @property int|null    $created_by_id
 * @property string|null $created_by_type
 */
class Notification extends MongoModel
{
    protected $connection = 'mongodb';
    protected $collection = 'notifications';

    protected $fillable = [
        'user_id',
        'user_type',
        'activity_type',
        'model_type',
        'model_id',
        'message',
        'status',
        'dismiss_status',
        'created_by_type',
        'created_by_id',
    ];

    protected $attributes = [
        'status'         => 'UNREAD',
        'dismiss_status' => 'PENDING',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ── Scopes ──────────────────────────────────────────────────────────────

    public function scopeUnread($query)
    {
        return $query->where('status', 'UNREAD');
    }

    public function scopeUndismissed($query)
    {
        return $query->where('dismiss_status', 'PENDING');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ── Cross-database relations (MongoDB → PostgreSQL) ─────────────────────

    /**
     * The user this notification belongs to (PostgreSQL users table).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The user who triggered this notification (PostgreSQL users table).
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Resolve the subject model (Document, Folder, etc.) from PostgreSQL.
     * MongoDB doesn't natively support morphTo across drivers, so we resolve
     * the subject manually when needed.
     */
    public function getSubjectAttribute(): mixed
    {
        if (empty($this->model_type) || empty($this->model_id)) {
            return null;
        }

        $modelClass = $this->model_type;
        if (class_exists($modelClass)) {
            return $modelClass::find($this->model_id);
        }

        return null;
    }

    // ── Model events ────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::saved(function ($notification) {
            self::bustCache($notification);
        });

        static::updated(function ($notification) {
            self::bustCache($notification);
        });

        static::deleted(function ($notification) {
            self::bustCache($notification);
        });
    }

    private static function bustCache(self $notification): void
    {
        $userId = $notification->user_id ?? Auth::id();
        if ($userId) {
            Cache::forget('dashboard_data_' . $userId);
        }
        Cache::forget('notifications');
    }
}
