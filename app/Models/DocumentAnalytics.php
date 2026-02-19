<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model as MongoModel;

/**
 * MongoDB-backed Document Analytics model.
 *
 * Captures every view, download, share, and print event for a document.
 * Stored in the `edms_nosql` MongoDB database under the `document_analytics`
 * collection.  Because event frequency can be very high, MongoDB is the
 * correct store for this data — no schema migrations needed as event types
 * evolve over time.
 *
 * Usage:
 *   DocumentAnalytics::record($documentId, 'view', auth()->id());
 *
 * Aggregation example (top 10 downloaded docs this month):
 *   DocumentAnalytics::where('event', 'download')
 *       ->where('created_at', '>=', now()->startOfMonth())
 *       ->groupBy('document_id')
 *       ->selectRaw('document_id, count(*) as total')
 *       ->orderByDesc('total')
 *       ->limit(10)
 *       ->get();
 *
 * @property string      $_id          MongoDB ObjectId
 * @property int         $document_id  FK → documents.id (PostgreSQL)
 * @property int|null    $user_id      FK → users.id (PostgreSQL), null = guest
 * @property string      $event        view | download | share | print | preview
 * @property string|null $ip_address   Hashed or masked for GDPR compliance
 * @property string|null $user_agent   Browser/device fingerprint
 * @property array       $metadata     Arbitrary contextual data (page, query, etc.)
 */
class DocumentAnalytics extends MongoModel
{
    protected $connection = 'mongodb';
    protected $collection = 'document_analytics';

    protected $fillable = [
        'document_id',
        'user_id',
        'event',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'document_id' => 'integer',
            'user_id'     => 'integer',
            'metadata'    => 'array',
            'created_at'  => 'datetime',
            'updated_at'  => 'datetime',
        ];
    }

    // ── Factory method ───────────────────────────────────────────────────────

    /**
     * Record an analytics event for a document.
     *
     * @param  int         $documentId
     * @param  string      $event       view|download|share|print|preview
     * @param  int|null    $userId
     * @param  array       $metadata    Extra context (folder_id, search_query, etc.)
     */
    public static function record(
        int    $documentId,
        string $event,
        ?int   $userId   = null,
        array  $metadata = []
    ): self {
        return static::create([
            'document_id' => $documentId,
            'user_id'     => $userId,
            'event'       => $event,
            'ip_address'  => request()?->ip(),
            'user_agent'  => request()?->userAgent(),
            'metadata'    => $metadata,
        ]);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForDocument($query, int $documentId)
    {
        return $query->where('document_id', $documentId);
    }

    public function scopeEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeThisMonth($query)
    {
        return $query->where('created_at', '>=', now()->startOfMonth());
    }

    public function scopeThisWeek($query)
    {
        return $query->where('created_at', '>=', now()->startOfWeek());
    }

    // ── Cross-database relations (MongoDB → PostgreSQL) ──────────────────────

    /**
     * The document this event belongs to (PostgreSQL documents table).
     */
    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    /**
     * The user who triggered this event (PostgreSQL users table).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ── Aggregation helpers ──────────────────────────────────────────────────

    /**
     * Count events by type for a given document.
     *
     * @return array{views: int, downloads: int, shares: int, prints: int}
     */
    public static function summaryForDocument(int $documentId): array
    {
        $events = ['view', 'download', 'share', 'print', 'preview'];
        $summary = [];

        foreach ($events as $event) {
            $summary[$event . 's'] = static::forDocument($documentId)
                ->event($event)
                ->count();
        }

        return $summary;
    }
}
