<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hasil extends Model
{
    use HasFactory;

    protected $table = 'hasil';

    protected $fillable = [
        'id_masjid',
        'tarikh',
        'no_resit',
        'id_akaun',
        'id_sumber_hasil',
        'amaun_tunai',
        'amaun_online',
        'jumlah',
        'id_tabung_khas',
        'id_program',
        'jenis_jumaat',
        'catatan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tarikh' => 'date',
            'amaun_tunai' => 'decimal:2',
            'amaun_online' => 'decimal:2',
            'jumlah' => 'decimal:2',
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

    public function sumberHasil(): BelongsTo
    {
        return $this->belongsTo(SumberHasil::class, 'id_sumber_hasil');
    }

    public function tabungKhas(): BelongsTo
    {
        return $this->belongsTo(TabungKhas::class, 'id_tabung_khas');
    }

    public function programMasjid(): BelongsTo
    {
        return $this->belongsTo(ProgramMasjid::class, 'id_program');
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

    public function scopeByAkaun(Builder $query, int $idAkaun): Builder
    {
        return $query->where('id_akaun', $idAkaun);
    }

    public function scopeBySumber(Builder $query, int $idSumber): Builder
    {
        return $query->where('id_sumber_hasil', $idSumber);
    }

    public function scopeJumaat(Builder $query): Builder
    {
        return $query->whereNotNull('jenis_jumaat');
    }
}
