<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CmsPage extends Model
{
    use HasFactory;

    protected $table = 'pages';

    protected $fillable = [
        'masjid_id',
        'page_name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function masjid(): BelongsTo
    {
        return $this->belongsTo(Masjid::class, 'masjid_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CmsSection::class, 'page_id')->orderBy('sort_order');
    }
}
