<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RunningNo extends Model
{
    use HasFactory;

    protected $table = 'running_no';

    public $incrementing = false;

    protected $fillable = [
        'id_masjid',
        'prefix',
        'tahun',
        'bulan',
        'last_no',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'bulan' => 'integer',
            'last_no' => 'integer',
        ];
    }

    public function masjid(): BelongsTo
    {
        return $this->belongsTo(Masjid::class, 'id_masjid');
    }

    public function scopeForPeriod(Builder $query, int $idMasjid, string $prefix, int $tahun, int $bulan): Builder
    {
        return $query->where('id_masjid', $idMasjid)
            ->where('prefix', $prefix)
            ->where('tahun', $tahun)
            ->where('bulan', $bulan);
    }
}
