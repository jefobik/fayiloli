<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * TenantStatus — lifecycle state machine for multi-tenant provisioning.
 *
 * Allowed transitions:
 *
 *   PENDING  → ACTIVE     (Activate   — provisioning accepted)
 *   PENDING  → INACTIVE   (Reject     — provisioning denied / cancelled)
 *   ACTIVE   → SUSPENDED  (Suspend    — policy / payment violation)
 *   ACTIVE   → INACTIVE   (Deactivate — graceful shutdown)
 *   SUSPENDED → ACTIVE    (Reactivate — issue resolved)
 *   SUSPENDED → INACTIVE  (Archive    — permanent suspension)
 *   INACTIVE  → ACTIVE    (Reactivate — re-enable a previously deactivated tenant)
 *
 * `is_active` on the Tenant model is always derived from status:
 *   ACTIVE → true   |   all other states → false
 */
enum TenantStatus: string
{
    case PENDING   = 'pending';
    case ACTIVE    = 'active';
    case SUSPENDED = 'suspended';
    case INACTIVE  = 'inactive';

    // ── Human-readable labels ────────────────────────────────────────────────

    public function label(): string
    {
        return match ($this) {
            self::PENDING   => 'Pending Activation',
            self::ACTIVE    => 'Active',
            self::SUSPENDED => 'Suspended',
            self::INACTIVE  => 'Inactive',
        };
    }

    // ── Bootstrap badge CSS class ────────────────────────────────────────────

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING   => 'bg-warning text-dark',
            self::ACTIVE    => 'bg-success',
            self::SUSPENDED => 'bg-danger',
            self::INACTIVE  => 'bg-secondary',
        };
    }

    // ── Font Awesome icon name (without the fa- prefix) ──────────────────────

    public function icon(): string
    {
        return match ($this) {
            self::PENDING   => 'clock',
            self::ACTIVE    => 'circle-check',
            self::SUSPENDED => 'ban',
            self::INACTIVE  => 'circle-xmark',
        };
    }

    // ── Whether this status means the tenant's EDMS is accessible ────────────

    public function isAccessible(): bool
    {
        return $this === self::ACTIVE;
    }

    // ── State machine ─────────────────────────────────────────────────────────

    /**
     * Returns transitions reachable from the current status.
     *
     * Each entry is an associative array:
     *   - target   TenantStatus  the destination status
     *   - action   string        verb shown on the action button
     *   - btnClass string        Bootstrap button variant
     *
     * @return array<array{target: TenantStatus, action: string, btnClass: string}>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PENDING => [
                ['target' => self::ACTIVE,    'action' => 'Activate',   'btnClass' => 'btn-success'],
                ['target' => self::INACTIVE,  'action' => 'Reject',     'btnClass' => 'btn-outline-secondary'],
            ],
            self::ACTIVE => [
                ['target' => self::SUSPENDED, 'action' => 'Suspend',    'btnClass' => 'btn-warning'],
                ['target' => self::INACTIVE,  'action' => 'Deactivate', 'btnClass' => 'btn-outline-secondary'],
            ],
            self::SUSPENDED => [
                ['target' => self::ACTIVE,    'action' => 'Reactivate', 'btnClass' => 'btn-success'],
                ['target' => self::INACTIVE,  'action' => 'Archive',    'btnClass' => 'btn-outline-secondary'],
            ],
            self::INACTIVE => [
                ['target' => self::ACTIVE,    'action' => 'Reactivate', 'btnClass' => 'btn-success'],
            ],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        foreach ($this->allowedTransitions() as $t) {
            if ($t['target'] === $target) {
                return true;
            }
        }

        return false;
    }

    /**
     * Past-tense label for the action that brings a tenant INTO this status.
     * Used in flash messages and audit notes.
     */
    public function incomingActionLabel(): string
    {
        return match ($this) {
            self::ACTIVE    => 'Activated',
            self::SUSPENDED => 'Suspended',
            self::INACTIVE  => 'Deactivated',
            self::PENDING   => 'Reset to Pending',
        };
    }

    // ── Filament / Spatie compatibility aliases ───────────────────────────────

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING   => 'warning',
            self::ACTIVE    => 'success',
            self::SUSPENDED => 'danger',
            self::INACTIVE  => 'gray',
        };
    }
}
