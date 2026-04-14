<?php

namespace App\Scopes;

use App\Tenant\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Automatically restricts Eloquent queries to the active tenant's masjid.
 *
 * Applied globally to all models that use the HasMasjidScope trait.
 *
 * Behavior:
 *   - Bypassed when TenantContext::isBypassed() is true (SuperAdmin).
 *   - Bypassed when no tenant has been resolved (CLI, queue, unauthenticated).
 *   - Qualifies the column with the model's table name to avoid ambiguous joins.
 */
class MasjidScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // SuperAdmin or CLI: do not filter
        if (TenantContext::isBypassed() || ! TenantContext::isResolved()) {
            return;
        }

        $builder->where(
            $model->getTable() . '.id_masjid',
            TenantContext::get()
        );
    }
}
