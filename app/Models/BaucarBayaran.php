<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BaucarBayaran extends Model
{
    use HasFactory;

    protected $table = 'baucar_bayaran';

    protected $fillable = [
        'id_masjid',
        'tarikh',
        'no_baucar',
        'id_akaun',
        'kaedah',
        'no_rujukan',
        'jumlah',
        'catatan',
        'status',
        'created_by',
        'dilulus_oleh',
        'tarikh_lulus',
    ];

    protected function casts(): array
    {
        return [
            'tarikh' => 'date',
            'jumlah' => 'decimal:2',
            'tarikh_lulus' => 'datetime',
        ];
    }

    public function masjid(): BelongsTo
    {
        return $this->belongsTo(Masjid::class, 'id_masjid');
    }

    public function akaun(): BelongsTo
    {
        return $this->belongsTo(Akaun::class, 'id_akaun');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dilulusOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dilulus_oleh');
    }

    public function belanja(): HasMany
    {
        return $this->hasMany(Belanja::class, 'id_baucar');
    }

    public function scopeByMasjid(Builder $query, int $idMasjid): Builder
    {
        return $query->where('id_masjid', $idMasjid);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'DRAF');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'LULUS');
    }

    public function scopeBetweenDates(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('tarikh', [$from, $to]);
    }
}
