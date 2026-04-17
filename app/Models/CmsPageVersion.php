<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsPageVersion extends Model
{
    use HasFactory;

    protected $table = 'cms_page_versions';

    protected $fillable = [
        'cms_page_id',
        'masjid_id',
        'slug',
        'version_no',
        'title',
        'seo_title',
        'seo_meta_description',
        'content_json',
        'is_active',
        'action',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'content_json' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(CmsBuilderPage::class, 'cms_page_id');
    }

    public function masjid(): BelongsTo
    {
        return $this->belongsTo(Masjid::class, 'masjid_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
