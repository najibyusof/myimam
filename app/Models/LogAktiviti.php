<?php

namespace App\Models;

use App\Traits\HasMasjidScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAktiviti extends Model
{
    use HasFactory, HasMasjidScope;

    protected $table = 'log_aktiviti';

    const UPDATED_AT = null;

    protected $fillable = [
        'id_masjid',
        'id_user',
        'jenis',
        'modul',
        'aksi',
        'rujukan_id',
        'butiran',
        'data_lama',
        'data_baru',
        'ip',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'data_lama' => 'array',
            'data_baru' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function masjid(): BelongsTo
    {
        return $this->belongsTo(Masjid::class, 'id_masjid');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function scopeJenis(Builder $query, string $jenis): Builder
    {
        return $query->where('jenis', $jenis);
    }

    public function scopeBetweenCreatedAt(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}
