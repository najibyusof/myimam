<?php

namespace App\Models;

use App\Traits\HasMasjidScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriBelanja extends Model
{
    use HasFactory, HasMasjidScope;

    protected $table = 'kategori_belanja';

    protected $fillable = [
        'id_masjid',
        'kod',
        'nama_kategori',
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

    public function belanja(): HasMany
    {
        return $this->hasMany(Belanja::class, 'id_kategori_belanja');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }
}
