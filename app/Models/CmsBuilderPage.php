<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CmsBuilderPage extends Model
{
    use HasFactory;

    protected $table = 'cms_pages';

    protected $fillable = [
        'masjid_id',
        'slug',
        'title',
        'seo_title',
        'seo_meta_description',
        'content_json',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'content_json' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function masjid(): BelongsTo
    {
        return $this->belongsTo(Masjid::class, 'masjid_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(CmsPageVersion::class, 'cms_page_id')->orderByDesc('version_no');
    }
}
