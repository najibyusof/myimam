<?php

namespace App\Models;

use App\Traits\HasMasjidScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Akaun extends Model
{
    use HasFactory, HasMasjidScope;

    protected $table = 'akaun';

    protected $fillable = [
        'id_masjid',
        'nama_akaun',
        'jenis',
        'no_akaun',
        'nama_bank',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
        ];
    }

    public function masjid(): BelongsTo
    {
        return $this->belongsTo(Masjid::class, 'id_masjid');
    }

    public function hasil(): HasMany
    {
        return $this->hasMany(Hasil::class, 'id_akaun');
    }

    public function belanja(): HasMany
    {
        return $this->hasMany(Belanja::class, 'id_akaun');
    }

    public function baucarBayaran(): HasMany
    {
        return $this->hasMany(BaucarBayaran::class, 'id_akaun');
    }

    public function pindahanKeluar(): HasMany
    {
        return $this->hasMany(PindahanAkaun::class, 'dari_akaun_id');
    }

    public function pindahanMasuk(): HasMany
    {
        return $this->hasMany(PindahanAkaun::class, 'ke_akaun_id');
    }

    public function akaunTujuan(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'pindahan_akaun', 'dari_akaun_id', 'ke_akaun_id')
            ->withPivot(['id_masjid', 'tarikh', 'amaun', 'catatan', 'created_by'])
            ->withTimestamps();
    }

    public function akaunSumber(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'pindahan_akaun', 'ke_akaun_id', 'dari_akaun_id')
            ->withPivot(['id_masjid', 'tarikh', 'amaun', 'catatan', 'created_by'])
            ->withTimestamps();
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status_aktif', true);
    }

    public function scopeJenis(Builder $query, string $jenis): Builder
    {
        return $query->where('jenis', $jenis);
    }

    public function scopeTunai(Builder $query): Builder
    {
        return $query->where('jenis', 'tunai');
    }
}
