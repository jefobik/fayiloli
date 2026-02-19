<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use MongoDB\Laravel\Eloquent\Model as MongoModel;
use Spatie\Activitylog\Contracts\Activity as ActivityContract;

/**
 * MongoDB-backed Activity Log model.
 *
 * Replaces Spatie's default PostgreSQL-backed Activity model.
 * Stored in the `edms_nosql` MongoDB database under the `activity_log`
 * collection. Implements Spatie\Activitylog\Contracts\Activity exactly
 * so all package helpers work transparently.
 *
 * Cross-database morphTo relations work because Eloquent resolves the
 * connection from the target model class (User/Document/Folder all use
 * pgsql), not from this model's connection.
 *
 * @property string      $_id          MongoDB ObjectId
 * @property string      $log_name     e.g. "document", "folder", "user"
 * @property string      $description  e.g. "created", "updated", "deleted"
 * @property string|null $subject_type Morph type of the subject model
 * @property mixed|null  $subject_id   Morph id of the subject model
 * @property string|null $causer_type  Morph type of the causer (User)
 * @property mixed|null  $causer_id    Morph id of the causer (User)
 * @property Collection  $properties   Arbitrary JSON payload
 * @property string|null $batch_uuid   Groups related log entries
 * @property string|null $event        e.g. "created", "updated", "deleted"
 */
class MongoActivity extends MongoModel implements ActivityContract
{
    protected $connection = 'mongodb';
    protected $collection = 'activity_log';

    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'batch_uuid',
        'event',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'collection',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // ── ActivityContract — required method signatures ─────────────────────────

    /**
     * The model that was acted upon.
     * morphTo() resolves the target model's own connection (pgsql),
     * so cross-database lookups work transparently.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The user who caused the activity.
     * morphTo() resolves to App\Models\User which uses the pgsql connection.
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @param  string $propertyName
     * @param  mixed  $defaultValue
     */
    public function getExtraProperty(string $propertyName, mixed $defaultValue = null): mixed
    {
        return $this->properties?->get($propertyName) ?? $defaultValue;
    }

    public function changes(): Collection
    {
        if (! $this->properties?->has('attributes')) {
            return collect();
        }

        return collect([
            'attributes' => $this->properties->get('attributes', []),
            'old'        => $this->properties->get('old', []),
        ]);
    }

    // ── Scopes — exact signatures from ActivityContract ───────────────────────

    public function scopeInLog(Builder $query, ...$logNames): Builder
    {
        // Accept both ->inLog('a', 'b') and ->inLog(['a', 'b'])
        if (isset($logNames[0]) && is_array($logNames[0])) {
            $logNames = $logNames[0];
        }

        return $query->whereIn('log_name', $logNames);
    }

    public function scopeCausedBy(Builder $query, Model $causer): Builder
    {
        return $query
            ->where('causer_type', $causer->getMorphClass())
            ->where('causer_id', $causer->getKey());
    }

    public function scopeForSubject(Builder $query, Model $subject): Builder
    {
        return $query
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey());
    }

    public function scopeForEvent(Builder $query, string $event): Builder
    {
        return $query->where('event', $event);
    }
}
