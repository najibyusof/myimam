<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSubscription extends Model
{
    use HasFactory;

    protected $table = 'tenant_subscriptions';

    protected $fillable = [
        'masjid_id',
        'plan_id',
        'start_date',
        'end_date',
        'status',
        'grace_days',
        'amount_paid',
        'payment_reference',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date'  => 'date',
            'end_date'    => 'date',
            'amount_paid' => 'decimal:2',
            'grace_days'  => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function masjid(): BelongsTo
    {
        return $this->belongsTo(Masjid::class, 'masjid_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'expired');
    }

    public function scopeExpiringBefore(Builder $query, Carbon $date): Builder
    {
        return $query->where('end_date', '<=', $date->toDateString())
            ->where('status', 'active');
    }

    public function scopeForMasjid(Builder $query, int $masjidId): Builder
    {
        return $query->where('masjid_id', $masjidId);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired'
            || ($this->end_date !== null && $this->end_date->isPast());
    }

    /**
     * Still within grace window after the subscription end date.
     */
    public function isInGrace(): bool
    {
        if (! $this->isExpired()) {
            return false;
        }

        return Carbon::now()->lessThanOrEqualTo(
            $this->end_date->addDays($this->grace_days)
        );
    }

    public function daysRemaining(): int
    {
        if ($this->end_date === null) {
            return PHP_INT_MAX;
        }

        return (int) max(0, Carbon::now()->diffInDays($this->end_date, false));
    }
}
