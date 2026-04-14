<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SumberHasil extends Model
{
    use HasFactory;

    protected $table = 'sumber_hasil';

    protected $fillable = [
        'id_masjid',
        'kod',
        'nama_sumber',
        'jenis',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function masjid(): BelongsTo
    {
        return $this->belongsTo(Masjid::class, 'id_masjid');
    }

    public function hasil(): HasMany
    {
        return $this->hasMany(Hasil::class, 'id_sumber_hasil');
    }

    public function scopeByMasjid(Builder $query, int $idMasjid): Builder
    {
        return $query->where('id_masjid', $idMasjid);
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }
}
