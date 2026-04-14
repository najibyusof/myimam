<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $table = 'subscription_plans';

    protected $fillable = [
        'name',
        'slug',
        'price',
        'billing_cycle',
        'duration_months',
        'features',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price'           => 'decimal:2',
            'features'        => 'array',
            'is_active'       => 'boolean',
            'duration_months' => 'integer',
            'sort_order'      => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function tenantSubscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class, 'plan_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Check whether a specific feature key is enabled in this plan.
     */
    public function hasFeature(string $key): bool
    {
        return (bool) ($this->features[$key] ?? false);
    }

    /**
     * Get a feature value (supports both boolean flags and numeric limits).
     */
    public function feature(string $key, mixed $default = null): mixed
    {
        return $this->features[$key] ?? $default;
    }
}
