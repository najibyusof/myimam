<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Tenant-aware Role model extending Spatie's Role.
 *
 * Hierarchy levels:
 *   1 = system (reserved — cannot be deleted)
 *   2 = admin-level (only SuperAdmin can manage)
 *   3 = user-level  (Masjid Admin can manage within their tenant)
 *
 * Scoping:
 *   masjid_id = NULL  → global/system role (visible/usable by all masjids)
 *   masjid_id = X     → custom role scoped purely to masjid X
 */
class Role extends SpatieRole
{
    // ─── Override fillable ────────────────────────────────────────────────────

    protected $fillable = [
        'name',
        'guard_name',
        'masjid_id',
        'level',
    ];

    protected function casts(): array
    {
        return [
            'masjid_id' => 'integer',
            'level'     => 'integer',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function masjid(): BelongsTo
    {
        return $this->belongsTo(Masjid::class, 'masjid_id');
    }

    // ─── Query scopes ─────────────────────────────────────────────────────────

    /** Roles belonging to a specific masjid. */
    public function scopeByMasjid(Builder $query, int $masjidId): Builder
    {
        return $query->where('masjid_id', $masjidId);
    }

    /** Global (null masjid_id) roles — shared across all tenants. */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('masjid_id');
    }

    /** Roles at a specific hierarchy level. */
    public function scopeForLevel(Builder $query, int $level): Builder
    {
        return $query->where('level', $level);
    }

    /**
     * Roles the given actor is authorised to VIEW/MANAGE.
     *
     * – SuperAdmin → all roles
     * – Masjid Admin → only their own masjid's roles (masjid_id = actor.id_masjid)
     * – Others → nothing
     */
    public function scopeVisibleTo(Builder $query, User $actor): Builder
    {
        if (self::actorIsSuperAdmin($actor)) {
            return $query;
        }

        if ($actor->hasRole('Admin') && !empty($actor->id_masjid)) {
            return $query->where('masjid_id', $actor->id_masjid);
        }

        return $query->whereRaw('1 = 0');
    }

    // ─── Helper predicates ───────────────────────────────────────────────────

    /** Role belongs to no specific masjid (system/global). */
    public function isGlobal(): bool
    {
        return is_null($this->masjid_id);
    }

    /** Role is at the admin-hierarchy level (level == 2). */
    public function isAdminLevel(): bool
    {
        return (int) $this->level === 2;
    }

    /** Role is at the protected system level (level == 1). Cannot be deleted. */
    public function isSystemLevel(): bool
    {
        return (int) $this->level === 1;
    }

    /**
     * Whether this role can be created/edited/deleted by the given actor.
     *
     * SuperAdmin: can manage all roles except system-level deletion.
     * Masjid Admin: can only manage their own masjid's level-3 roles.
     */
    public function canBeManagedBy(User $actor): bool
    {
        if (self::actorIsSuperAdmin($actor)) {
            return !$this->isSystemLevel();
        }

        if ($actor->hasRole('Admin') && !empty($actor->id_masjid)) {
            return !$this->isGlobal()
                && (int) $this->masjid_id === (int) $actor->id_masjid
                && (int) $this->level >= 3;
        }

        return false;
    }

    // ─── Static helpers ───────────────────────────────────────────────────────

    public static function actorIsSuperAdmin(User $actor): bool
    {
        return $actor->peranan === 'superadmin'
            || $actor->hasRole('superadmin')
            || $actor->hasRole('SuperAdmin');
    }

    /** Human-readable level labels. */
    public static function levelLabel(int $level): string
    {
        return match ($level) {
            1 => 'System',
            2 => 'Admin',
            3 => 'User',
            default => "Level {$level}",
        };
    }

    /** Tailwind badge classes per level. */
    public static function levelBadgeClass(int $level): string
    {
        return match ($level) {
            1 => 'bg-red-100 text-red-700',
            2 => 'bg-indigo-100 text-indigo-700',
            3 => 'bg-emerald-100 text-emerald-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }
}
