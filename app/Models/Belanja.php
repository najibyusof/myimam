<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Belanja extends Model
{
    use HasFactory;

    protected $table = 'belanja';

    protected $fillable = [
        'id_masjid',
        'tarikh',
        'id_akaun',
        'id_kategori_belanja',
        'amaun',
        'id_tabung_khas',
        'id_program',
        'penerima',
        'catatan',
        'bukti_fail',
        'created_by',
        'status',
        'id_baucar',
        'is_deleted',
        'deleted_by',
        'deleted_at',
        'dilulus_oleh',
        'tarikh_lulus',
    ];

    protected function casts(): array
    {
        return [
            'tarikh' => 'date',
            'amaun' => 'decimal:2',
            'is_deleted' => 'boolean',
            'deleted_at' => 'datetime',
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

    public function kategoriBelanja(): BelongsTo
    {
        return $this->belongsTo(KategoriBelanja::class, 'id_kategori_belanja');
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

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function dilulusOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dilulus_oleh');
    }

    public function baucar(): BelongsTo
    {
        return $this->belongsTo(BaucarBayaran::class, 'id_baucar');
    }

    public function scopeByMasjid(Builder $query, int $idMasjid): Builder
    {
        return $query->where('id_masjid', $idMasjid);
    }

    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    public function scopeDeleted(Builder $query): Builder
    {
        return $query->where('is_deleted', true);
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

    public function scopeForBaucar(Builder $query, int $idBaucar): Builder
    {
        return $query->where('id_baucar', $idBaucar);
    }
}
