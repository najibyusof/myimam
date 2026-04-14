<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Masjid extends Model
{
    use HasFactory;

    protected $table = 'masjid';

    protected $fillable = [
        'nama',
        'code',
        'alamat',
        'daerah',
        'negeri',
        'no_pendaftaran',
        'tarikh_daftar',
        'status',
        'subscription_status',
        'subscription_expiry',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tarikh_daftar'       => 'date',
            'subscription_expiry' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Status helpers
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscription_status === 'active'
            && ($this->subscription_expiry === null || $this->subscription_expiry->isFuture());
    }

    public function isInGracePeriod(): bool
    {
        if ($this->subscription_status !== 'expired' || $this->subscription_expiry === null) {
            return false;
        }

        $activeSubscription = $this->activeSubscription;
        $graceDays = $activeSubscription?->grace_days ?? 0;

        return Carbon::now()->lessThanOrEqualTo(
            $this->subscription_expiry->addDays($graceDays)
        );
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeSuspended(Builder $query): Builder
    {
        return $query->where('status', 'suspended');
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class, 'masjid_id');
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(TenantSubscription::class, 'masjid_id')
            ->where('status', 'active')
            ->latestOfMany('start_date');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'id_masjid');
    }

    public function akaun(): HasMany
    {
        return $this->hasMany(Akaun::class, 'id_masjid');
    }

    public function sumberHasil(): HasMany
    {
        return $this->hasMany(SumberHasil::class, 'id_masjid');
    }

    public function kategoriBelanja(): HasMany
    {
        return $this->hasMany(KategoriBelanja::class, 'id_masjid');
    }

    public function tabungKhas(): HasMany
    {
        return $this->hasMany(TabungKhas::class, 'id_masjid');
    }

    public function programMasjid(): HasMany
    {
        return $this->hasMany(ProgramMasjid::class, 'id_masjid');
    }

    public function hasil(): HasMany
    {
        return $this->hasMany(Hasil::class, 'id_masjid');
    }

    public function belanja(): HasMany
    {
        return $this->hasMany(Belanja::class, 'id_masjid');
    }

    public function baucarBayaran(): HasMany
    {
        return $this->hasMany(BaucarBayaran::class, 'id_masjid');
    }

    public function logAktiviti(): HasMany
    {
        return $this->hasMany(LogAktiviti::class, 'id_masjid');
    }

    public function pindahanAkaun(): HasMany
    {
        return $this->hasMany(PindahanAkaun::class, 'id_masjid');
    }

    public function runningNo(): HasMany
    {
        return $this->hasMany(RunningNo::class, 'id_masjid');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('nama', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
                ->orWhere('daerah', 'like', "%{$term}%")
                ->orWhere('negeri', 'like', "%{$term}%")
                ->orWhere('no_pendaftaran', 'like', "%{$term}%");
        });
    }
}
