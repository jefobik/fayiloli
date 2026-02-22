<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * TenantModule — Represents individually toggleable EDMS feature modules.
 *
 * Each case maps to a group of routes in routes/tenant.php.
 * The `settings.modules` JSONB array on the Tenant model stores the
 * string values of enabled modules. Absent key → defaults() apply.
 */
enum TenantModule: string
{
    case DOCUMENTS     = 'documents';
    case FOLDERS       = 'folders';
    case TAGS          = 'tags';
    case USERS         = 'users';
    case NOTIFICATIONS = 'notifications';
    case FILE_REQUESTS = 'file_requests';
    case SHARES        = 'shares';
    case PROJECTS      = 'projects';
    case HRM      = 'human resource management';
    case STATS      = 'statistical reports';

    // ── Labels & UI ──────────────────────────────────────────────────────────

    public function label(): string
    {
        return match ($this) {
            self::DOCUMENTS     => 'Documents',
            self::FOLDERS       => 'Folders',
            self::TAGS          => 'Tags',
            self::USERS         => 'User Management',
            self::NOTIFICATIONS => 'Notifications',
            self::FILE_REQUESTS => 'File Requests',
            self::SHARES        => 'Document Sharing',
            self::PROJECTS      => 'Projects',
            self::HRM      => 'Human Resource Management',
            self::STATS      => 'Statistical Reports',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::DOCUMENTS     => 'Upload, view, and manage documents.',
            self::FOLDERS       => 'Organise documents into folder hierarchies.',
            self::TAGS          => 'Tag and filter documents by custom labels.',
            self::USERS         => 'Create and manage tenant user accounts.',
            self::NOTIFICATIONS => 'In-app notification centre.',
            self::FILE_REQUESTS => 'Request files from other users.',
            self::SHARES        => 'Share documents via secure public links.',
            self::PROJECTS      => 'Project tracking and overview.',
            self::HRM      => 'Manage human resources.',
            self::STATS      => 'View statistical reports.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DOCUMENTS     => 'file-lines',
            self::FOLDERS       => 'folder-open',
            self::TAGS          => 'tags',
            self::USERS         => 'users-gear',
            self::NOTIFICATIONS => 'bell',
            self::FILE_REQUESTS => 'file-circle-question',
            self::SHARES        => 'share-nodes',
            self::PROJECTS      => 'diagram-project',
            self::HRM      => 'users',
            self::STATS      => 'chart-line',
        };
    }

    /**
     * The route name prefix (or URI prefix) used for this module's landing
     * page — used to build workspace quick-launch links.
     */
    public function landingRoute(): string
    {
        return match ($this) {
            self::DOCUMENTS     => 'documents.index',
            self::FOLDERS       => 'folders.index',
            self::TAGS          => 'tags.index',
            self::USERS         => 'users.index',
            self::NOTIFICATIONS => 'notifications.fetch',
            self::FILE_REQUESTS => 'documents.index',   // no standalone index yet
            self::SHARES        => 'documents.index',   // no standalone index yet
            self::PROJECTS      => 'projects.index',
            self::HRM      => 'hrm.index',
            self::STATS      => 'stats.index',
        };
    }

    // ── Defaults ─────────────────────────────────────────────────────────────

    /**
     * Modules enabled by default when a tenant is provisioned without an
     * explicit module selection.
     *
     * @return list<string>
     */
    public static function defaults(): array
    {
        return [
            self::DOCUMENTS->value,
            self::FOLDERS->value,
            self::TAGS->value,
            self::USERS->value,
            self::NOTIFICATIONS->value,
        ];
    }

    /**
     * All module values as a simple array — useful for validation rules.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
