<?php

namespace App\Traits;

use App\Scopes\MasjidScope;
use App\Tenant\TenantContext;
use Illuminate\Database\Eloquent\Builder;

/**
 * Adds multi-tenant scoping to Eloquent models that carry an id_masjid column.
 *
 * Features:
 *  - Registers MasjidScope as a global scope so all queries are automatically
 *    filtered by the active tenant unless scoping is bypassed (SuperAdmin).
 *  - Provides scopeByMasjid() for explicit filtering when needed.
 *  - Provides scopeWithoutTenant() to drop the global scope ad-hoc.
 */
trait HasMasjidScope
{
    /**
     * Register the global tenant scope on boot.
     */
    public static function bootHasMasjidScope(): void
    {
        static::addGlobalScope(new MasjidScope());
    }

    // -------------------------------------------------------------------------
    // Query Scopes
    // -------------------------------------------------------------------------

    /**
     * Explicitly filter by a given masjid ID, regardless of the global scope.
     *
     * Usage: Model::withoutTenantScope()->byMasjid($id)->get()
     *        or simply Model::byMasjid($id)->get() (redundant if scope already active)
     */
    public function scopeByMasjid(Builder $query, int $idMasjid): Builder
    {
        return $query->where($this->getTable() . '.id_masjid', $idMasjid);
    }

    /**
     * Remove the automatic MasjidScope for this query chain.
     * Useful when SuperAdmin code runs through standard service classes.
     *
     * Usage: Model::withoutTenantScope()->get()
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(MasjidScope::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Return the tenant ID that is currently active in this request context.
     */
    public static function currentMasjidId(): ?int
    {
        return TenantContext::get();
    }
}
