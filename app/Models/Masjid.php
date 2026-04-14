<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Masjid extends Model
{
    use HasFactory;

    protected $table = 'masjid';

    protected $fillable = [
        'nama',
        'alamat',
        'daerah',
        'negeri',
        'no_pendaftaran',
        'tarikh_daftar',
    ];

    protected function casts(): array
    {
        return [
            'tarikh_daftar' => 'date',
        ];
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
                ->orWhere('daerah', 'like', "%{$term}%")
                ->orWhere('negeri', 'like', "%{$term}%")
                ->orWhere('no_pendaftaran', 'like', "%{$term}%");
        });
    }
}
