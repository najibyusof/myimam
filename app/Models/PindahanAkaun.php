<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PindahanAkaun extends Model
{
    use HasFactory;

    protected $table = 'pindahan_akaun';

    protected $fillable = [
        'id_masjid',
        'tarikh',
        'dari_akaun_id',
        'ke_akaun_id',
        'amaun',
        'catatan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tarikh' => 'date',
            'amaun' => 'decimal:2',
        ];
    }

    public function masjid(): BelongsTo
    {
        return $this->belongsTo(Masjid::class, 'id_masjid');
    }

    public function dariAkaun(): BelongsTo
    {
        return $this->belongsTo(Akaun::class, 'dari_akaun_id');
    }

    public function keAkaun(): BelongsTo
    {
        return $this->belongsTo(Akaun::class, 'ke_akaun_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByMasjid(Builder $query, int $idMasjid): Builder
    {
        return $query->where('id_masjid', $idMasjid);
    }

    public function scopeBetweenDates(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('tarikh', [$from, $to]);
    }

    public function scopeForAkaun(Builder $query, int $idAkaun): Builder
    {
        return $query->where(function (Builder $q) use ($idAkaun) {
            $q->where('dari_akaun_id', $idAkaun)
                ->orWhere('ke_akaun_id', $idAkaun);
        });
    }
}
