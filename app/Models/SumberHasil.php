<?php

namespace App\Models;

use App\Traits\HasMasjidScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SumberHasil extends Model
{
    use HasFactory, HasMasjidScope;

    protected $table = 'sumber_hasil';

    protected $fillable = [
        'id_masjid',
        'kod',
        'nama_sumber',
        'jenis',
        'aktif',
        'is_baseline',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'is_baseline' => 'boolean',
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

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }
}
