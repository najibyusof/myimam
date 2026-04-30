<?php

namespace App\Models;

use App\Traits\HasNotifications;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasNotifications;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_masjid',
        'name',
        'email',
        'password',
        'peranan',
        'aktif',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'aktif' => 'boolean',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function hasTwoFactorEnabled(): bool
    {
        return !empty($this->two_factor_secret) && !is_null($this->two_factor_confirmed_at);
    }

    public function masjid(): BelongsTo
    {
        return $this->belongsTo(Masjid::class, 'id_masjid');
    }

    public function createdHasil(): HasMany
    {
        return $this->hasMany(Hasil::class, 'created_by');
    }

    public function createdBelanja(): HasMany
    {
        return $this->hasMany(Belanja::class, 'created_by');
    }

    public function approvedBelanja(): HasMany
    {
        return $this->hasMany(Belanja::class, 'dilulus_oleh');
    }

    public function deletedBelanja(): HasMany
    {
        return $this->hasMany(Belanja::class, 'deleted_by');
    }

    public function createdBaucar(): HasMany
    {
        return $this->hasMany(BaucarBayaran::class, 'created_by');
    }

    public function approvedBaucar(): HasMany
    {
        return $this->hasMany(BaucarBayaran::class, 'dilulus_oleh');
    }

    public function logAktiviti(): HasMany
    {
        return $this->hasMany(LogAktiviti::class, 'id_user');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }

    public function scopeByMasjid(Builder $query, int $idMasjid): Builder
    {
        return $query->where('id_masjid', $idMasjid);
    }

    public function scopeVisibleTo(Builder $query, User $actor): Builder
    {
        if ($actor->peranan === 'superadmin') {
            return $query;
        }

        if (!$actor->id_masjid) {
            return $query->whereRaw('1 = 0');
        }

        return $query->byMasjid($actor->id_masjid);
    }

    public function scopePeranan(Builder $query, string $role): Builder
    {
        return $query->where('peranan', $role);
    }
}
